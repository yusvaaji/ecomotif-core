<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Booking;
use Modules\Car\Entities\Car;

class MarketingController extends Controller
{
    /**
     * Marketing Dashboard
     * GET /api/user/marketing/dashboard
     */
    public function dashboard(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Check if user is marketing (linked to showroom)
        if (!$user->isMarketing()) {
            return response()->json([
                'message' => trans('translate.Only marketing can access this route')
            ], 403);
        }

        // Get statistics
        $total_applications = Booking::where('marketing_id', $user->id)->count();
        $pending_applications = Booking::where('marketing_id', $user->id)
            ->where('leasing_status', Booking::LEASING_STATUS_PENDING)
            ->count();
        $approved_applications = Booking::where('marketing_id', $user->id)
            ->where('leasing_status', Booking::LEASING_STATUS_APPROVED)
            ->count();

        // Get recent applications
        $recent_applications = Booking::with('car', 'consumer', 'showroom')
            ->where('marketing_id', $user->id)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'user' => $user,
            'showroom' => $user->showroom,
            'statistics' => [
                'total_applications' => $total_applications,
                'pending_applications' => $pending_applications,
                'approved_applications' => $approved_applications,
            ],
            'recent_applications' => $recent_applications,
        ]);
    }

    /**
     * Create application for consumer
     * POST /api/user/marketing/applications
     */
    public function createApplication(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user->isMarketing()) {
            return response()->json([
                'message' => trans('translate.Only marketing can create applications')
            ], 403);
        }

        $rules = [
            'consumer_name' => 'required|string|max:255',
            'consumer_email' => 'required|email',
            'consumer_phone' => 'required|string',
            'car_id' => 'required|integer|exists:cars,id',
            'down_payment' => 'required|numeric|min:0',
            'installment_amount' => 'required|numeric|min:0',
        ];

        $this->validate($request, $rules);

        $car = Car::findOrFail($request->car_id);

        // Create or get consumer user
        $consumer = User::firstOrCreate(
            ['email' => $request->consumer_email],
            [
                'name' => $request->consumer_name,
                'phone' => $request->consumer_phone,
                'username' => \Str::slug($request->consumer_name).'-'.date('Ymdhis'),
                'status' => 'enable',
                'is_banned' => 'no',
                'password' => \Hash::make(\Str::random(10)),
            ]
        );

        // Use showroom_id from marketing user
        $showroom_id = $user->showroom_id ?? $car->agent_id;

        // Create booking/application
        $application = new Booking();
        $application->order_id = substr(rand(0, time()), 0, 10);
        $application->user_id = $consumer->id;
        $application->supplier_id = $car->agent_id;
        $application->car_id = $car->id;
        $application->marketing_id = $user->id;
        $application->showroom_id = $showroom_id;
        $application->application_type = Booking::APPLICATION_TYPE_LEASING;
        $application->down_payment = $request->down_payment;
        $application->installment_amount = $request->installment_amount;
        $application->price = $car->regular_price;
        $application->leasing_status = Booking::LEASING_STATUS_PENDING;
        $application->status = Booking::STATUS_PENDING;
        $application->save();

        return response()->json([
            'message' => trans('translate.Application created successfully'),
            'application' => $application->load('car', 'consumer', 'showroom'),
        ]);
    }

    /**
     * List applications for marketing (Unclaimed + Claimed by this user)
     * GET /api/user/marketing/orders
     */
    public function orders(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user->isMarketing()) {
            return response()->json([
                'message' => trans('translate.Only marketing can access applications')
            ], 403);
        }

        $showroom_id = $user->showroom_id;

        $applications = Booking::with('car.brand', 'consumer', 'showroom')
            ->where(function($query) use ($showroom_id) {
                $query->where('showroom_id', $showroom_id)
                    ->orWhere('supplier_id', $showroom_id);
            })
            // Must not be claimed by another sales
            ->where(function($query) use ($user) {
                $query->whereNull('marketing_id')
                      ->orWhere('marketing_id', $user->id);
            })
            // If unclaimed, do not show cancelled/rejected (status 3 or 4)
            ->where(function($query) {
                $query->whereNotNull('marketing_id')
                      ->orWhereNotIn('status', ['3', '4']);
            });

        // Filter by status if provided
        if ($request->has('status')) {
            $applications->where('status', $request->status);
        }

        $applications = $applications->orderBy('id', 'desc')->get();

        return response()->json([
            'applications' => $applications,
        ]);
    }

    /**
     * Get order details
     * GET /api/user/marketing/orders/{id}
     */
    public function orderDetails($id)
    {
        $user = Auth::guard('api')->user();

        if (!$user->isMarketing()) {
            return response()->json([
                'message' => trans('translate.Only marketing can access order details')
            ], 403);
        }

        $showroom_id = $user->showroom_id;

        $application = Booking::with('car.brand', 'consumer', 'showroom')
            ->where(function($query) use ($showroom_id) {
                $query->where('showroom_id', $showroom_id)
                    ->orWhere('supplier_id', $showroom_id);
            })
            ->where('id', $id)
            ->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        // Check if claimed by another sales
        if ($application->marketing_id != null && $application->marketing_id != $user->id) {
            return response()->json([
                'message' => 'Pesanan ini sudah diambil oleh sales lain'
            ], 403);
        }

        return response()->json([
            'application' => $application,
        ]);
    }

    /**
     * Claim order
     * POST /api/user/marketing/orders/{id}/claim
     */
    public function claimOrder(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        if (!$user->isMarketing()) {
            return response()->json([
                'message' => trans('translate.Only marketing can claim applications')
            ], 403);
        }

        $application = Booking::where(function($query) use ($user) {
            $query->where('showroom_id', $user->showroom_id)
                ->orWhere('supplier_id', $user->showroom_id);
        })->where('id', $id)->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        if ($application->status == '3' || $application->status == '4') {
            return response()->json([
                'message' => 'Pesanan yang sudah dibatalkan tidak bisa diambil'
            ], 400);
        }

        if ($application->marketing_id != null) {
            if ($application->marketing_id == $user->id) {
                return response()->json([
                    'message' => 'Anda sudah mengambil pesanan ini'
                ], 400);
            }
            return response()->json([
                'message' => 'Pesanan sudah diambil oleh sales lain'
            ], 403);
        }

        $application->marketing_id = $user->id;
        $application->save();

        return response()->json([
            'message' => 'Pesanan berhasil diklaim',
            'application' => $application,
        ]);
    }

    /**
     * Update order status
     * POST /api/user/marketing/orders/{id}/status
     */
    public function updateStatus(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        if (!$user->isMarketing()) {
            return response()->json([
                'message' => trans('translate.Only marketing can update applications')
            ], 403);
        }

        $request->validate([
            'status' => 'required|string|in:1,2,5' // 5=Dihubungi, 1=Disetujui/Diproses, 2=Selesai
        ]);

        $application = Booking::where(function($query) use ($user) {
            $query->where('showroom_id', $user->showroom_id)
                ->orWhere('supplier_id', $user->showroom_id);
        })->where('id', $id)->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        if ($application->marketing_id != $user->id) {
            return response()->json([
                'message' => 'Anda belum mengambil pesanan ini atau pesanan diambil oleh sales lain'
            ], 403);
        }

        $application->status = $request->status;
        $application->save();

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui',
            'application' => $application,
        ]);
    }
}




