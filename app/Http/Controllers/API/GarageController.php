<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\GarageService;
use App\Models\ServiceBooking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GarageController extends Controller
{
    // ──────────────────────────────────────────────
    // PUBLIC: Customer-facing endpoints
    // ──────────────────────────────────────────────

    /**
     * GET /api/garages
     */
    public function index(Request $request)
    {
        $selectCols = [
            'users.id', 'users.name', 'users.username', 'users.designation', 
            'users.specialization', 'users.image', 'users.address', 'users.email', 
            'users.phone', 'users.latitude', 'users.longitude'
        ];

        $garages = User::leftJoin('merchant_profiles', function($join) {
                $join->on('users.id', '=', 'merchant_profiles.user_id')
                     ->where('merchant_profiles.business_type', \App\Models\MerchantProfile::BUSINESS_GARAGE);
            })
            ->where(['users.status' => 'enable', 'users.is_banned' => 'no', 'users.is_garage' => 1])
            ->where('users.email_verified_at', '!=', null);

        // Fetch user vehicles to determine brand match priority
        $user = auth('api')->user();
        $userBrandNames = [];
        if ($user) {
            $userVehicles = \App\Models\UserVehicle::with('brand')->where('user_id', $user->id)->get();
            foreach ($userVehicles as $uv) {
                if ($uv->brand) {
                    $userBrandNames[] = strtolower($uv->brand->name);
                }
            }
            \Log::info('Garage Filtering for User ID: ' . $user->id, ['brands' => $userBrandNames]);
        } else {
            \Log::info('Garage Filtering for Guest User (No Brands)');
        }

        $prioritySql = "1"; // Default for select column
        if (!empty($userBrandNames)) {
            $cases = [];
            foreach ($userBrandNames as $brandName) {
                if (trim($brandName) === '') continue;
                $escaped = addslashes(trim($brandName));
                $cases[] = "LOWER(merchant_profiles.served_brands) LIKE '%{$escaped}%'";
            }
            if (!empty($cases)) {
                $filterCondition = implode(" OR ", $cases);
                \Log::info('Garage Filter SQL Condition: ' . $filterCondition);
                // Strict filter: only show garages that serve the user's brands
                $garages->whereRaw("($filterCondition)");
            }
            $prioritySql = "1";
        }
        $selectCols[] = \DB::raw("$prioritySql AS brand_match_priority");

        if ($request->filled('search')) {
            $garages->where(function ($q) use ($request) {
                $q->where('users.name', 'like', '%'.$request->search.'%')
                    ->orWhere('users.specialization', 'like', '%'.$request->search.'%');
            });
        }

        if ($request->filled('location')) {
            $garages->whereHas('garageServices', function ($q) {
                // If there's location filter logic, it goes here
            });
        }

        if ($request->filled('min_rating')) {
            $minRating = (float) $request->min_rating;
            $garages->whereHas('reviews', function ($q) use ($minRating) {
                $q->havingRaw('AVG(rating) >= ?', [$minRating]);
            });
        }

        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $minRadius = (float) ($request->min_radius_km ?? 0);
            $maxRadius = (float) ($request->radius_km ?? 50);

            $haversine = "(6371 * acos(cos(radians($lat)) * cos(radians(users.latitude)) * cos(radians(users.longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(users.latitude))))";

            $garages->whereNotNull('users.latitude')
                ->whereNotNull('users.longitude')
                ->whereRaw("$haversine >= ?", [$minRadius])
                ->whereRaw("$haversine <= ?", [$maxRadius]);

            $selectCols[] = \DB::raw("$haversine AS distance");
            
            // Prioritize brand match first, then by nearest distance
            $garages->orderByRaw("brand_match_priority DESC, $haversine ASC");
        } else {
            // Prioritize brand match first, then fallback to descending ID
            $garages->orderByRaw("brand_match_priority DESC, users.id DESC");
        }

        $garages->select($selectCols)
            ->withCount(['reviews', 'garageServices'])
            ->withAvg('reviews', 'rating');

        return response()->json([
            'garages' => $garages->paginate(12),
            'debug_user_brands' => $userBrandNames ?? [],
            'debug_sql' => $prioritySql ?? '',
        ]);
    }

    /**
     * GET /api/garages/{id}
     */
    public function show($id)
    {
        $garage = User::where(['status' => 'enable', 'is_banned' => 'no', 'is_garage' => 1])
            ->where('email_verified_at', '!=', null)
            ->with(['garageServices' => function ($q) {
                $q->where('status', 'active');
            }])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->find($id);

        if (! $garage) {
            return response()->json(['message' => trans('translate.Garage Not Found!')], 404);
        }

        return response()->json([
            'garage' => $garage,
        ]);
    }

    // ──────────────────────────────────────────────
    // CUSTOMER: Booking endpoints (auth:api)
    // ──────────────────────────────────────────────

    /**
     * POST /api/user/service-bookings
     */
    public function storeBooking(Request $request)
    {
        $user = Auth::guard('api')->user();

        $request->validate([
            'garage_id' => 'required|integer',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'integer|exists:garage_services,id',
            'service_type' => 'required|in:walk_in,home_service',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_address' => 'required_if:service_type,home_service|nullable|string',
            'customer_lat' => 'nullable|numeric',
            'customer_lng' => 'nullable|numeric',
            'location_benchmark' => 'nullable|string|max:255',
            'vehicle_brand' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|string|max:10',
            'vehicle_plate' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $services = GarageService::whereIn('id', $request->service_ids)
            ->where('garage_id', $request->garage_id)
            ->where('status', 'active')
            ->get();

        if ($services->isEmpty()) {
            return response()->json(['message' => 'No valid services selected.'], 400);
        }

        $totalPrice = $services->sum('price');

        $booking = ServiceBooking::create([
            'order_id' => 'SB-'.strtoupper(substr(uniqid(), -8)),
            'user_id' => $user->id,
            'garage_id' => $request->garage_id,
            'service_ids' => $request->service_ids,
            'service_type' => $request->service_type,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'customer_lat' => $request->customer_lat,
            'customer_lng' => $request->customer_lng,
            'location_benchmark' => $request->location_benchmark,
            'vehicle_brand' => $request->vehicle_brand,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_year' => $request->vehicle_year,
            'vehicle_plate' => $request->vehicle_plate,
            'notes' => $request->notes,
            'total_price' => $totalPrice,
            'status' => ServiceBooking::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => trans('translate.Booking created successfully'),
            'booking' => $booking->load('service', 'garage'),
        ], 201);
    }

    /**
     * POST /api/guest/service-bookings — same payload as authenticated booking; no JWT (guest).
     */
    public function storeGuestBooking(Request $request)
    {
        $request->validate([
            'garage_id' => 'required|integer',
            'service_ids' => 'required|array|min:1',
            'service_ids.*' => 'integer|exists:garage_services,id',
            'service_type' => 'required|in:walk_in,home_service',
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:30',
            'customer_address' => 'required_if:service_type,home_service|nullable|string',
            'customer_lat' => 'nullable|numeric',
            'customer_lng' => 'nullable|numeric',
            'location_benchmark' => 'nullable|string|max:255',
            'vehicle_brand' => 'nullable|string|max:100',
            'vehicle_model' => 'nullable|string|max:100',
            'vehicle_year' => 'nullable|string|max:10',
            'vehicle_plate' => 'nullable|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $services = GarageService::whereIn('id', $request->service_ids)
            ->where('garage_id', $request->garage_id)
            ->where('status', 'active')
            ->get();

        if ($services->isEmpty()) {
            return response()->json(['message' => 'No valid services selected.'], 400);
        }

        $totalPrice = $services->sum('price');

        $booking = ServiceBooking::create([
            'order_id' => 'SB-'.strtoupper(substr(uniqid(), -8)),
            'user_id' => null,
            'garage_id' => $request->garage_id,
            'service_ids' => $request->service_ids,
            'service_type' => $request->service_type,
            'booking_date' => $request->booking_date,
            'booking_time' => $request->booking_time,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_address' => $request->customer_address,
            'customer_lat' => $request->customer_lat,
            'customer_lng' => $request->customer_lng,
            'location_benchmark' => $request->location_benchmark,
            'vehicle_brand' => $request->vehicle_brand,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_year' => $request->vehicle_year,
            'vehicle_plate' => $request->vehicle_plate,
            'notes' => $request->notes,
            'total_price' => $totalPrice,
            'status' => ServiceBooking::STATUS_PENDING,
        ]);

        return response()->json([
            'message' => trans('translate.Booking created successfully'),
            'booking' => $booking->load('service', 'garage'),
        ], 201);
    }

    /**
     * GET /api/user/service-bookings
     */
    public function myBookings(Request $request)
    {
        $user = Auth::guard('api')->user();

        $query = ServiceBooking::with('service', 'garage')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json([
            'bookings' => $query->paginate(12),
        ]);
    }

    /**
     * GET /api/user/service-bookings/{id}
     */
    public function showBooking($id)
    {
        $user = Auth::guard('api')->user();

        $booking = ServiceBooking::with('service', 'garage')
            ->where('user_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'booking' => $booking,
        ]);
    }

    /**
     * POST /api/user/service-bookings/{id}/cancel
     */
    public function cancelBooking($id)
    {
        $user = Auth::guard('api')->user();

        $booking = ServiceBooking::where('user_id', $user->id)->findOrFail($id);

        if (! in_array($booking->status, [ServiceBooking::STATUS_PENDING, ServiceBooking::STATUS_CONFIRMED])) {
            return response()->json(['message' => trans('translate.Booking cannot be cancelled')], 403);
        }

        $booking->status = ServiceBooking::STATUS_CANCELLED;
        $booking->save();

        return response()->json([
            'message' => trans('translate.Booking cancelled'),
            'booking' => $booking,
        ]);
    }

    // ──────────────────────────────────────────────
    // GARAGE OWNER: Dashboard & management (middleware: garage)
    // ──────────────────────────────────────────────

    /**
     * GET /api/user/garage/dashboard
     */
    public function dashboard()
    {
        $user = Auth::guard('api')->user();

        $totalServices = GarageService::where('garage_id', $user->id)->count();
        $totalBookings = ServiceBooking::where('garage_id', $user->id)->count();
        $pendingBookings = ServiceBooking::where('garage_id', $user->id)->where('status', ServiceBooking::STATUS_PENDING)->count();
        $completedBookings = ServiceBooking::where('garage_id', $user->id)->where('status', ServiceBooking::STATUS_COMPLETED)->count();

        $recentBookings = ServiceBooking::with('service', 'customer')
            ->where('garage_id', $user->id)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'total_services' => $totalServices,
            'total_bookings' => $totalBookings,
            'pending_bookings' => $pendingBookings,
            'completed_bookings' => $completedBookings,
            'recent_bookings' => $recentBookings,
        ]);
    }

    /**
     * GET /api/user/garage/services
     */
    public function listServices()
    {
        $user = Auth::guard('api')->user();

        return response()->json([
            'services' => GarageService::where('garage_id', $user->id)->orderBy('id', 'desc')->get(),
        ]);
    }

    /**
     * POST /api/user/garage/services
     */
    public function storeService(Request $request)
    {
        $user = Auth::guard('api')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'price', 'duration']);
        $data['garage_id'] = $user->id;
        $data['status'] = 'active';

        if ($request->hasFile('image')) {
            $data['image'] = uploadFile($request->file('image'), 'uploads/garage-services');
        }

        $service = GarageService::create($data);

        return response()->json([
            'message' => trans('translate.Service created successfully'),
            'service' => $service,
        ], 201);
    }

    /**
     * PUT /api/user/garage/services/{id}
     */
    public function updateService(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        $service = GarageService::where('garage_id', $user->id)->findOrFail($id);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|string|max:100',
            'image' => 'nullable|image|max:2048',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data = $request->only(['name', 'description', 'price', 'duration', 'status']);

        if ($request->hasFile('image')) {
            $data['image'] = uploadFile($request->file('image'), 'uploads/garage-services', $service->image);
        }

        $service->update(array_filter($data, fn ($v) => $v !== null));

        return response()->json([
            'message' => trans('translate.Service updated'),
            'service' => $service->fresh(),
        ]);
    }

    /**
     * DELETE /api/user/garage/services/{id}
     */
    public function deleteService($id)
    {
        $user = Auth::guard('api')->user();

        $service = GarageService::where('garage_id', $user->id)->findOrFail($id);
        $service->delete();

        return response()->json(['message' => trans('translate.Service deleted')]);
    }

    /**
     * GET /api/user/garage/bookings
     */
    public function garageBookings(Request $request)
    {
        $user = Auth::guard('api')->user();

        $query = ServiceBooking::with('service', 'customer')
            ->where('garage_id', $user->id)
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        return response()->json([
            'bookings' => $query->paginate(12),
        ]);
    }

    /**
     * GET /api/mechanic/bookings
     */
    public function mechanicBookings(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Mengambil booking yang di assign ke mekanik ini
        $query = ServiceBooking::with('service', 'customer')
            ->where('mechanic_id', $user->id)
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }

        return response()->json([
            'bookings' => $query->paginate(12),
        ]);
    }

    /**
     * GET /api/user/mechanic/dashboard
     */
    public function mechanicDashboard(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Tugas Baru (Confirmed)
        $tugasBaru = ServiceBooking::with('service', 'customer')
            ->where('mechanic_id', $user->id)
            ->where('status', 'confirmed')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // Sedang Dikerjakan (on_the_way or in_progress)
        $dikerjakan = ServiceBooking::with('service', 'customer')
            ->where('mechanic_id', $user->id)
            ->whereIn('status', ['on_the_way', 'in_progress'])
            ->orderBy('id', 'desc')
            ->get();

        // Total Selesai
        $tugasSelesai = ServiceBooking::where('mechanic_id', $user->id)
            ->where('status', 'completed')
            ->count();

        // Total Pendapatan
        $totalPendapatan = ServiceBooking::where('mechanic_id', $user->id)
            ->where('status', 'completed')
            ->sum('total_price');

        return response()->json([
            'tugas_baru' => $tugasBaru,
            'dikerjakan' => $dikerjakan,
            'tugas_selesai' => $tugasSelesai,
            'total_pendapatan' => $totalPendapatan,
        ]);
    }

    /**
     * GET /api/user/garage/bookings/{id}
     */
    public function garageBookingDetail($id)
    {
        $user = Auth::guard('api')->user();

        $booking = ServiceBooking::with('service', 'customer')
            ->where('garage_id', $user->id)
            ->findOrFail($id);

        return response()->json([
            'booking' => $booking,
        ]);
    }

    /**
     * PUT /api/user/garage/bookings/{id}/status
     */
    public function updateBookingStatus(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        $booking = ServiceBooking::where(function($q) use ($user) {
            $q->where('garage_id', $user->id)
              ->orWhere('mechanic_id', $user->id);
        })->findOrFail($id);

        $request->validate([
            'status' => 'required|in:confirmed,on_the_way,in_progress,completed,cancelled',
            'garage_notes' => 'nullable|string|max:2000',
            'mechanic_id' => 'nullable|exists:users,id',
        ]);

        $allowedTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['on_the_way', 'in_progress', 'completed', 'cancelled'],
            'on_the_way' => ['in_progress', 'completed', 'cancelled'],
            'in_progress' => ['completed'],
        ];

        $allowed = $allowedTransitions[$booking->status] ?? [];

        if (! in_array($request->status, $allowed)) {
            return response()->json([
                'message' => trans('translate.Invalid status transition'),
            ], 422);
        }

        $booking->status = $request->status;
        if ($request->filled('garage_notes')) {
            $booking->garage_notes = $request->garage_notes;
        }

        if ($request->filled('mechanic_id')) {
            // Pastikan mechanic adalah milik garage ini
            $isMechanicValid = \App\Models\User::where('id', $request->mechanic_id)
                ->where('partner_id', $user->id)
                ->where('is_sales', 1)
                ->where('sales_partner_type', 'garage')
                ->exists();

            if (! $isMechanicValid) {
                return response()->json([
                    'message' => trans('translate.Invalid mechanic selected'),
                ], 422);
            }
            $booking->mechanic_id = $request->mechanic_id;
        }

        $booking->save();

        return response()->json([
            'message' => trans('translate.Booking status updated'),
            'booking' => $booking->load('service', 'customer'),
        ]);
    }

    // ──────────────────────────────────────────────
    // MECHANICS MANAGEMENT
    // ──────────────────────────────────────────────

    /**
     * Get Mechanics for this Garage
     * GET /api/user/garage/mechanics
     */
    public function getMechanics()
    {
        $user = Auth::guard('api')->user();
        if ($user->is_garage != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $mechanics = User::where('partner_id', $user->id)
            ->where('is_sales', 1)
            ->where('sales_partner_type', 'garage')
            ->get();

        return response()->json(['mechanics' => $mechanics]);
    }

    /**
     * Add Mechanic to this Garage
     * POST /api/user/garage/mechanics
     */
    public function addMechanic(Request $request)
    {
        $user = Auth::guard('api')->user();
        if ($user->is_garage != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        $mechanic = new User();
        $mechanic->name = $request->name;
        $mechanic->username = explode('@', $request->email)[0] . rand(1000, 9999);
        $mechanic->email = $request->email;
        $mechanic->phone = $request->phone;
        $mechanic->password = bcrypt($request->password);
        $mechanic->partner_id = $user->id;
        $mechanic->is_sales = 1;
        $mechanic->sales_partner_type = 'garage';
        $mechanic->status = User::STATUS_ACTIVE;
        $mechanic->email_verified_at = now(); // Skip verification
        $mechanic->save();

        return response()->json([
            'message' => 'Mekanik berhasil ditambahkan', 
            'mechanic' => $mechanic
        ]);
    }

    /**
     * Remove Mechanic from this Garage
     * DELETE /api/user/garage/mechanics/{id}
     */
    public function removeMechanic($id)
    {
        $user = Auth::guard('api')->user();
        if ($user->is_garage != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $mechanic = User::where('partner_id', $user->id)->where('id', $id)->first();
        if (!$mechanic) {
            return response()->json(['message' => 'Mekanik tidak ditemukan di bengkel ini'], 404);
        }

        $mechanic->partner_id = null;
        $mechanic->is_sales = 0;
        $mechanic->sales_partner_type = null;
        $mechanic->save();

        return response()->json(['message' => 'Mekanik berhasil dihapus dari bengkel']);
    }

    /**
     * Update Mechanic Status
     * PUT /api/user/garage/mechanics/{id}/status
     */
    public function updateMechanicStatus(Request $request, $id)
    {
        $user = Auth::guard('api')->user();
        if ($user->is_garage != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $mechanic = User::where('partner_id', $user->id)->where('id', $id)->first();
        if (!$mechanic) {
            return response()->json(['message' => 'Mekanik tidak ditemukan di bengkel ini'], 404);
        }

        $request->validate([
            'status' => 'required|in:enable,disable'
        ]);

        $mechanic->status = $request->status;
        $mechanic->save();

        return response()->json([
            'message' => 'Status mekanik berhasil diperbarui',
            'mechanic' => $mechanic
        ]);
    }

    /**
     * Get Garage Performance / Reports
     * GET /api/user/garage/performance
     */
    public function performance()
    {
        $user = Auth::guard('api')->user();
        if ($user->is_garage != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $totalOrders = ServiceBooking::where('garage_id', $user->id)->count();
        $successfulOrders = ServiceBooking::where('garage_id', $user->id)->where('status', 'completed')->count();
        $pendingOrders = ServiceBooking::where('garage_id', $user->id)->where('status', 'pending')->count();
        $cancelledOrders = ServiceBooking::where('garage_id', $user->id)->where('status', 'cancelled')->count();
        $totalRevenue = ServiceBooking::where('garage_id', $user->id)->where('status', 'completed')->sum('total_price');

        // Mechanic performance
        $mechanics = User::where('partner_id', $user->id)
            ->where('is_sales', 1)
            ->where('sales_partner_type', 'garage')
            ->get();

        $mechanicPerformance = [];
        foreach ($mechanics as $mechanic) {
            $mOrders = ServiceBooking::where('garage_id', $user->id)
                ->where('mechanic_id', $mechanic->id)
                ->where('status', 'completed')
                ->count();
            $mRevenue = ServiceBooking::where('garage_id', $user->id)
                ->where('mechanic_id', $mechanic->id)
                ->where('status', 'completed')
                ->sum('total_price');

            $mechanicPerformance[] = [
                'id' => $mechanic->id,
                'name' => $mechanic->name,
                'successful_orders' => $mOrders,
                'total_revenue' => $mRevenue,
            ];
        }

        return response()->json([
            'total_orders' => $totalOrders,
            'successful_orders' => $successfulOrders,
            'pending_orders' => $pendingOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_revenue' => $totalRevenue,
            'mechanic_performance' => $mechanicPerformance,
        ]);
    }
}
