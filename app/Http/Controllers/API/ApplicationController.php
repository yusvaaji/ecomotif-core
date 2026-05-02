<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use App\Services\LeasingQuoteService;
use App\Services\PaymentCapabilityCalculator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Car\Entities\Car;

class ApplicationController extends Controller
{
    protected $calculator;

    public function __construct(
        PaymentCapabilityCalculator $calculator,
        protected LeasingQuoteService $leasingQuoteService
    ) {
        $this->calculator = $calculator;
    }

    /**
     * Select showroom (enhance existing dealers endpoint)
     * GET /api/showrooms
     */
    public function selectShowroom(Request $request)
    {
        $selectCols = ['id', 'name', 'username', 'designation', 'image', 'address', 'email', 'phone', 'barcode', 'latitude', 'longitude'];

        $showrooms = User::where(['status' => 'enable', 'is_banned' => 'no', 'is_dealer' => 1])
            ->where('email_verified_at', '!=', null);

        if ($request->search) {
            $showrooms->where('name', 'like', '%'.$request->search.'%');
        }

        if ($request->location) {
            $showrooms->whereHas('cars', function ($query) use ($request) {
                $query->where('city_id', $request->location);
            });
        }

        if ($request->filled('min_rating')) {
            $minRating = (float) $request->min_rating;
            $showrooms->whereHas('reviews', function ($q) use ($minRating) {
                $q->havingRaw('AVG(rating) >= ?', [$minRating]);
            });
        }

        // Geo-distance filter (haversine) — requires lat, lng
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;

            $haversine = "(6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude))))";

            $selectCols[] = \DB::raw("$haversine AS distance");

            if ($request->filled('radius_km') || $request->filled('min_radius_km')) {
                $maxRadius = (float) ($request->radius_km ?? 50);
                $minRadius = (float) ($request->min_radius_km ?? 0);
                $showrooms->whereNotNull('latitude')
                    ->whereNotNull('longitude')
                    ->whereRaw("$haversine >= ?", [$minRadius])
                    ->whereRaw("$haversine <= ?", [$maxRadius]);
            }

            // Urutkan null latitude/longitude ke paling bawah, sisanya urut berdasarkan jarak
            $showrooms->orderByRaw("CASE WHEN latitude IS NULL OR longitude IS NULL THEN 1 ELSE 0 END, $haversine ASC");
        } else {
            $showrooms->orderBy('id', 'desc');
        }

        $showrooms->select($selectCols)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating');

        $showrooms = $showrooms->paginate(12);

        return response()->json([
            'showrooms' => $showrooms,
        ]);
    }

    /**
     * Calculate installment
     * POST /api/calculate-installment
     */
    public function selectDPAndInstallment(Request $request)
    {
        $rules = [
            'car_price' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'tenure_months' => 'nullable|integer|min:12|max:60',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
        ];

        $this->validate($request, $rules);

        try {
            $data = $this->leasingQuoteService->quote(
                (float) $request->car_price,
                (float) $request->down_payment,
                (int) ($request->tenure_months ?? 36),
                (float) ($request->interest_rate ?? 10)
            );
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 403);
        }

        return response()->json($data);
    }

    /**
     * Calculate payment capability
     * POST /api/calculate-payment-capability
     * This uses the CalculatorController, but kept here for consistency
     */
    public function calculatePaymentCapability(Request $request)
    {
        $rules = [
            'monthly_income' => 'required|numeric|min:0',
            'monthly_expenses' => 'required|numeric|min:0',
            'existing_loans' => 'required|numeric|min:0',
            'car_price' => 'required|numeric|min:0',
            'tenure_months' => 'nullable|integer|min:12|max:60',
        ];

        $this->validate($request, $rules);

        $tenureMonths = $request->tenure_months ?? 36;

        try {
            $result = $this->calculator->calculate(
                $request->monthly_income,
                $request->monthly_expenses,
                $request->existing_loans,
                $request->car_price,
                $tenureMonths
            );

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('translate.Error calculating payment capability'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all applications for the authenticated consumer
     * GET /api/user/applications
     *
     * Query params: status, leasing_status, page, per_page
     */
    public function myApplications(Request $request)
    {
        $user = Auth::guard('api')->user();

        $query = Booking::with('car.brand', 'showroom', 'mediator', 'marketing')
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leasing_status')) {
            $query->where('leasing_status', $request->leasing_status);
        }

        if ($request->filled('application_type')) {
            $query->where('application_type', $request->application_type);
        }

        $perPage = $request->input('per_page', 12);
        $applications = $query->paginate($perPage);

        return response()->json([
            'applications' => $applications,
        ]);
    }

    /**
     * Submit application
     * POST /api/applications
     */
    public function submitApplication(Request $request)
    {
        $user = Auth::guard('api')->user();

        $rules = [
            'car_id' => 'required|integer|exists:cars,id',
            'down_payment' => 'required|numeric|min:0',
            'installment_amount' => 'required|numeric|min:0',
            'showroom_id' => 'nullable|integer|exists:users,id',
            'payment_method' => 'nullable|string',
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ];

        $this->validate($request, $rules);

        $car = Car::findOrFail($request->car_id);

        // Determine showroom_id
        $showroom_id = $request->showroom_id ?? $car->agent_id;

        $paymentMethod = strtolower($request->payment_method ?? 'leasing');
        $applicationType = $paymentMethod === 'cash' ? 'cash' : Booking::APPLICATION_TYPE_LEASING;
        
        // Create booking/application
        $application = new Booking();
        $application->order_id = substr(rand(0, time()), 0, 10);
        $application->user_id = $user->id;
        $application->supplier_id = $car->agent_id;
        $application->car_id = $car->id;
        $application->showroom_id = $showroom_id;
        $application->application_type = $applicationType;
        $application->payment_method = $paymentMethod;
        $application->consumer_name = $request->name;
        $application->consumer_email = $request->email;
        $application->consumer_phone = $request->phone;
        $application->consumer_address = $request->address;
        $application->booking_note = $request->notes;
        $application->down_payment = $request->down_payment;
        $application->installment_amount = $request->installment_amount;
        $application->price = ($car->offer_price && $car->offer_price > 0) ? $car->offer_price : $car->regular_price;
        $application->leasing_status = Booking::LEASING_STATUS_PENDING;
        $application->status = Booking::STATUS_PENDING;
        $application->save();

        return response()->json([
            'message' => trans('translate.Application submitted successfully'),
            'application' => $application->load('car', 'showroom'),
        ]);
    }

    /**
     * Upload documents for application
     * POST /api/applications/{id}/documents
     */
    public function uploadDocuments(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        $application = Booking::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (! $application) {
            return response()->json([
                'message' => trans('translate.Application not found'),
            ], 404);
        }

        $rules = [
            'documents' => 'required|array',
            'documents.*' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ];

        $this->validate($request, $rules);

        $documentPaths = [];
        foreach ($request->file('documents') as $document) {
            $path = uploadFile($document, 'uploads/application-documents');
            $documentPaths[] = $path;
        }

        // Merge with existing documents
        $existingDocuments = $application->application_documents ?? [];
        $allDocuments = array_merge($existingDocuments, $documentPaths);

        $application->application_documents = $allDocuments;
        $application->save();

        return response()->json([
            'message' => trans('translate.Documents uploaded successfully'),
            'application' => $application,
        ]);
    }

    /**
     * Get application status
     * GET /api/applications/{id}
     */
    public function applicationStatus($id)
    {
        $user = Auth::guard('api')->user();

        $application = Booking::with('car', 'showroom', 'mediator', 'marketing')
            ->where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (! $application) {
            return response()->json([
                'message' => trans('translate.Application not found'),
            ], 404);
        }

        return response()->json([
            'application' => $application,
        ]);
    }

    /**
     * Pay DP (Down Payment)
     * POST /api/applications/{id}/pay-dp
     */
    public function payDP(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        $application = Booking::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (! $application) {
            return response()->json([
                'message' => trans('translate.Application not found'),
            ], 404);
        }

        // Check if application is approved
        if ($application->leasing_status != Booking::LEASING_STATUS_APPROVED) {
            return response()->json([
                'message' => trans('translate.Application must be approved before paying DP'),
            ], 403);
        }

        // TODO: Integrate with payment gateway
        // For now, just mark as paid (actual implementation depends on payment flow)

        return response()->json([
            'message' => trans('translate.DP payment initiated'),
            'application' => $application,
        ]);
    }

    /**
     * Cancel Application
     * POST /api/applications/{id}/cancel
     */
    public function cancelApplication(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        $application = Booking::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        if ($application->status == Booking::STATUS_COMPLETED || $application->status == Booking::STATUS_APPROVED) {
            return response()->json([
                'message' => trans('translate.Cannot cancel a completed or approved application')
            ], 403);
        }

        $application->status = Booking::STATUS_CANCELLED_BY_USER;
        $application->save();

        return response()->json([
            'message' => trans('translate.Application cancelled successfully'),
            'application' => $application,
        ]);
    }
}
