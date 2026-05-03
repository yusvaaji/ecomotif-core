<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Car\Entities\Car;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Get summary stats for admin dashboard.
     */
    public function dashboard_stats(Request $request)
    {
        $totalMitra = \App\Models\User::where(function($q) {
            $q->where('is_dealer', 1)->orWhere('is_garage', 1);
        })->count();
        
        // Count Cars using correct namespace from nwidart/laravel-modules
        $totalUnit = \Modules\Car\Entities\Car::count();
        
        $totalUser = \App\Models\User::where('is_dealer', 0)
            ->where('is_garage', 0)
            ->where('is_sales', 0)
            ->count();
            
        $totalTransaksi = \App\Models\Booking::count() + \App\Models\ServiceBooking::count();

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_mitra' => $totalMitra,
                'total_unit' => $totalUnit,
                'total_user' => $totalUser,
                'total_transaksi' => $totalTransaksi,
            ]
        ]);
    }

    /**
     * Get all subscription plans for admin
     */
    public function subscription_plans(Request $request)
    {
        // Using correct namespace
        $plans = \Modules\Subscription\Entities\SubscriptionPlan::orderBy('plan_type', 'asc')->orderBy('serial', 'asc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $plans
        ]);
    }

    /**
     * Store a new subscription plan
     */
    public function store_subscription_plan(Request $request)
    {
        $plan = new \Modules\Subscription\Entities\SubscriptionPlan();
        $plan->plan_name = $request->plan_name;
        $plan->plan_price = $request->plan_price;
        $plan->plan_type = $request->plan_type ?? 'showroom_baru';
        $plan->expiration_date = $request->expiration_date ?? 'monthly';
        $plan->serial = $request->serial ?? 0;
        $plan->max_car = $request->max_car ?? 0;
        $plan->featured_car = $request->featured_car ?? 0;
        $plan->max_user = $request->max_user ?? 1;
        $plan->mitra_type = $request->mitra_type;
        $plan->category = $request->category;
        $plan->vehicle_type = $request->vehicle_type;
        $plan->status = $request->status ?? 'active';
        $plan->save();

        return response()->json([
            'status' => 'success',
            'data' => $plan
        ]);
    }

    /**
     * Update an existing subscription plan
     */
    public function update_subscription_plan(Request $request, $id)
    {
        $plan = \Modules\Subscription\Entities\SubscriptionPlan::find($id);
        if (!$plan) {
            return response()->json(['status' => 'error', 'message' => 'Plan not found'], 404);
        }

        if ($request->has('plan_name')) $plan->plan_name = $request->plan_name;
        if ($request->has('plan_price')) $plan->plan_price = $request->plan_price;
        if ($request->has('plan_type')) $plan->plan_type = $request->plan_type;
        if ($request->has('expiration_date')) $plan->expiration_date = $request->expiration_date;
        if ($request->has('serial')) $plan->serial = $request->serial;
        if ($request->has('max_car')) $plan->max_car = $request->max_car;
        if ($request->has('featured_car')) $plan->featured_car = $request->featured_car;
        if ($request->has('max_user')) $plan->max_user = $request->max_user;
        if ($request->has('mitra_type')) $plan->mitra_type = $request->mitra_type;
        if ($request->has('category')) $plan->category = $request->category;
        if ($request->has('vehicle_type')) $plan->vehicle_type = $request->vehicle_type;
        if ($request->has('status')) $plan->status = $request->status;
        $plan->save();

        return response()->json([
            'status' => 'success',
            'data' => $plan
        ]);
    }

    /**
     * Delete a subscription plan
     */
    public function destroy_subscription_plan($id)
    {
        $plan = \Modules\Subscription\Entities\SubscriptionPlan::find($id);
        if (!$plan) {
            return response()->json(['status' => 'error', 'message' => 'Plan not found'], 404);
        }

        // Check if plan has been purchased
        $purchase_qty = \Modules\Subscription\Entities\SubscriptionHistory::where('subscription_plan_id', $id)->count();
        if ($purchase_qty > 0) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Paket ini tidak bisa dihapus karena sudah ada mitra yang berlangganan.'
            ], 400);
        }

        $plan->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Paket berhasil dihapus'
        ]);
    }

    /**
     * Get all ads banners for admin
     */
    public function ads_banners_list()
    {
        $banners = \App\Models\AdsBanner::orderBy('id', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $banners]);
    }

    /**
     * Store new ads banner
     */
    public function store_ads_banner(Request $request)
    {
        $request->validate([
            'image' => 'required|image',
            'position' => 'required'
        ]);

        $banner = new \App\Models\AdsBanner();
        $banner->position = $request->position;
        $banner->position_key = \Illuminate\Support\Str::slug($request->position, '_');
        $banner->link = $request->link ?? '';
        $banner->status = $request->status ?? 'enable';

        if ($request->hasFile('image')) {
            $ext = $request->file('image')->getClientOriginalExtension();
            $imageName = 'banner_' . time() . '.' . $ext;
            $request->file('image')->move(public_path('uploads/custom-images'), $imageName);
            $banner->image = 'uploads/custom-images/' . $imageName;
        }
        $banner->save();

        return response()->json(['status' => 'success', 'data' => $banner]);
    }

    /**
     * Delete ads banner
     */
    public function destroy_ads_banner($id)
    {
        $banner = \App\Models\AdsBanner::find($id);
        if ($banner) {
            if ($banner->image && file_exists(public_path($banner->image))) {
                @unlink(public_path($banner->image));
            }
            $banner->delete();
        }
        return response()->json(['status' => 'success']);
    }

    /**
     * Get active ads banners for mobile app users
     */
    public function get_active_banners()
    {
        $banners = \App\Models\AdsBanner::where('status', 'enable')->orderBy('id', 'desc')->get();
        return response()->json(['status' => 'success', 'data' => $banners]);
    }

    /**
     * Get pending cars that need verification
     */
    public function pending_cars(Request $request)
    {
        // Admin middleware / guard should protect this route
        
        $query = Car::with([
            'dealer' => function ($q) {
                $q->select('id', 'name', 'email', 'image', 'address', 'partner_id', 'kyc_status', 'is_dealer', 'is_garage', 'phone', 'operating_hours');
            },
            'dealer.merchantProfile.subscriptionPlan',
            'dealer.showroom' => function ($q) {
                $q->select('id', 'name', 'address');
            },
            'brand',
            'galleries'
        ]);

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhereHas('dealer', function($q2) use ($search) {
                      $q2->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Status filter
        if ($request->has('status') && !empty($request->status) && strtolower($request->status) !== 'semua') {
            $statusStr = strtolower($request->status);
            if ($statusStr === 'menunggu verifikasi') $statusStr = 'pending';
            if ($statusStr === 'diverifikasi') $statusStr = 'approved';
            if ($statusStr === 'ditolak') $statusStr = 'rejected';
            
            $query->where('approved_by_admin', $statusStr);
        }

        // Year range filter
        if ($request->has('min_year') && !empty($request->min_year)) {
            $query->where('year', '>=', $request->min_year);
        }
        if ($request->has('max_year') && !empty($request->max_year)) {
            $query->where('year', '<=', $request->max_year);
        }

        // Price range filter
        if ($request->has('min_price') && !empty($request->min_price)) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && !empty($request->max_price)) {
            $query->where('price', '<=', $request->max_price);
        }

        $cars = $query->orderByRaw("FIELD(approved_by_admin, 'pending') DESC") // Prioritaskan pending di atas
            ->orderBy('id', 'desc')
            ->paginate(30);

        return response()->json([
            'status' => 'success',
            'cars' => $cars
        ]);
    }

    /**
     * Verify a car (approve or reject)
     */
    public function verify_car(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $car = Car::find($id);
        if (!$car) {
            return response()->json(['message' => 'Car not found'], 404);
        }

        $car->approved_by_admin = $request->status;
        
        // Also update the main status if approved
        if ($request->status == 'approved') {
            $car->status = 'enable';
        }

        $car->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Car verification status updated successfully',
            'car' => $car
        ]);
    }

    /**
     * Get list of partners (Showroom and Garage)
     */
    public function mitra_list(Request $request)
    {
        $query = \App\Models\User::with(['merchantProfile.subscriptionPlan'])
            ->where(function($q) {
                $q->where('is_dealer', 1)->orWhere('is_garage', 1);
            });

        // Search filter
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->has('type') && !empty($request->type) && $request->type !== 'semua') {
            if ($request->type == 'showroom') {
                $query->where('is_dealer', 1);
            } elseif ($request->type == 'bengkel') {
                $query->where('is_garage', 1);
            }
        }

        // Status filter
        if ($request->has('status') && !empty($request->status) && $request->status !== 'semua') {
            if ($request->status == 'verified') {
                $query->where('kyc_status', 'enable');
            } elseif ($request->status == 'pending') {
                $query->where('kyc_status', 'disable');
            }
        }

        $mitra = $query->orderBy('id', 'desc')->paginate(30);

        return response()->json([
            'status' => 'success',
            'mitra' => $mitra
        ]);
    }

    /**
     * Verify a partner (Showroom or Garage)
     */
    public function verify_mitra(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:enable,disable'
        ]);

        $mitra = \App\Models\User::find($id);
        if (!$mitra) {
            return response()->json(['message' => 'Mitra not found'], 404);
        }

        $mitra->kyc_status = $request->status;
        $mitra->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Mitra verification status updated successfully',
            'mitra' => $mitra
        ]);
    }
}
