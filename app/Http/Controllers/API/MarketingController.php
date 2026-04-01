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
     * List applications created by marketing
     * GET /api/user/marketing/applications
     */
    public function applications(Request $request)
    {
        $user = Auth::guard('api')->user();

        if (!$user->isMarketing()) {
            return response()->json([
                'message' => trans('translate.Only marketing can access applications')
            ], 403);
        }

        $applications = Booking::with('car', 'consumer', 'showroom')
            ->where('marketing_id', $user->id);

        // Filter by status
        if ($request->leasing_status) {
            $applications->where('leasing_status', $request->leasing_status);
        }

        $applications = $applications->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'applications' => $applications,
        ]);
    }
}




