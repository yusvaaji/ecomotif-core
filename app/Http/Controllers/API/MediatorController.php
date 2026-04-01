<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Booking;
use Modules\Car\Entities\Car;

class MediatorController extends Controller
{
    /**
     * Mediator Dashboard
     * GET /api/user/mediator/dashboard
     */
    public function dashboard(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_mediator != 1) {
            return response()->json([
                'message' => trans('translate.Only mediator can access this route')
            ], 403);
        }

        // Get statistics
        $total_applications = Booking::where('mediator_id', $user->id)->count();
        $pending_applications = Booking::where('mediator_id', $user->id)
            ->where('leasing_status', Booking::LEASING_STATUS_PENDING)
            ->count();
        $approved_applications = Booking::where('mediator_id', $user->id)
            ->where('leasing_status', Booking::LEASING_STATUS_APPROVED)
            ->count();
        $rejected_applications = Booking::where('mediator_id', $user->id)
            ->where('leasing_status', Booking::LEASING_STATUS_REJECTED)
            ->count();

        // Get recent applications
        $recent_applications = Booking::with('car', 'consumer', 'showroom')
            ->where('mediator_id', $user->id)
            ->orderBy('id', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'user' => $user,
            'statistics' => [
                'total_applications' => $total_applications,
                'pending_applications' => $pending_applications,
                'approved_applications' => $approved_applications,
                'rejected_applications' => $rejected_applications,
            ],
            'recent_applications' => $recent_applications,
        ]);
    }

    /**
     * List all applications created by mediator
     * GET /api/user/mediator/applications
     */
    public function applications(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_mediator != 1) {
            return response()->json([
                'message' => trans('translate.Only mediator can access this route')
            ], 403);
        }

        $applications = Booking::with('car', 'consumer', 'showroom')
            ->where('mediator_id', $user->id);

        // Filter by status
        if ($request->leasing_status) {
            $applications->where('leasing_status', $request->leasing_status);
        }

        // Filter by application_type
        if ($request->application_type) {
            $applications->where('application_type', $request->application_type);
        }

        $applications = $applications->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'applications' => $applications,
        ]);
    }

    /**
     * Create new leasing application for consumer
     * POST /api/user/mediator/applications
     */
    public function createApplication(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_mediator != 1) {
            return response()->json([
                'message' => trans('translate.Only mediator can create applications')
            ], 403);
        }

        $rules = [
            'consumer_name' => 'required|string|max:255',
            'consumer_email' => 'required|email',
            'consumer_phone' => 'required|string',
            'car_id' => 'required|integer|exists:cars,id',
            'down_payment' => 'required|numeric|min:0',
            'installment_amount' => 'required|numeric|min:0',
            'showroom_id' => 'nullable|integer|exists:users,id',
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
                'password' => \Hash::make(\Str::random(10)), // Random password, consumer can reset later
            ]
        );

        // Determine showroom_id
        $showroom_id = $request->showroom_id ?? $car->agent_id;

        // Create booking/application
        $application = new Booking();
        $application->order_id = substr(rand(0, time()), 0, 10);
        $application->user_id = $consumer->id;
        $application->supplier_id = $car->agent_id;
        $application->car_id = $car->id;
        $application->mediator_id = $user->id;
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
     * Get application details
     * GET /api/user/mediator/applications/{id}
     */
    public function applicationDetails($id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_mediator != 1) {
            return response()->json([
                'message' => trans('translate.Only mediator can access application details')
            ], 403);
        }

        $application = Booking::with('car', 'consumer', 'showroom')
            ->where('mediator_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        return response()->json([
            'application' => $application,
        ]);
    }

    /**
     * Update application
     * PUT /api/user/mediator/applications/{id}
     */
    public function updateApplication(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_mediator != 1) {
            return response()->json([
                'message' => trans('translate.Only mediator can update applications')
            ], 403);
        }

        $application = Booking::where('mediator_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        // Only allow update if status is pending
        if ($application->leasing_status != Booking::LEASING_STATUS_PENDING) {
            return response()->json([
                'message' => trans('translate.Cannot update application that is already processed')
            ], 403);
        }

        if ($request->has('down_payment')) {
            $application->down_payment = $request->down_payment;
        }

        if ($request->has('installment_amount')) {
            $application->installment_amount = $request->installment_amount;
        }

        if ($request->has('showroom_id')) {
            $application->showroom_id = $request->showroom_id;
        }

        $application->save();

        return response()->json([
            'message' => trans('translate.Application updated successfully'),
            'application' => $application->load('car', 'consumer', 'showroom'),
        ]);
    }
}




