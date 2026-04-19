<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Booking;
use Modules\Car\Entities\Car;
use App\Services\PaymentCapabilityCalculator;

class ApplicationController extends Controller
{
    protected $calculator;

    public function __construct(PaymentCapabilityCalculator $calculator)
    {
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
            $showrooms->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->location) {
            $showrooms->whereHas('cars', function($query) use($request){
                $query->where('city_id', $request->location);
            });
        }

        if ($request->filled('min_rating')) {
            $minRating = (float) $request->min_rating;
            $showrooms->whereHas('reviews', function ($q) use ($minRating) {
                $q->havingRaw('AVG(rating) >= ?', [$minRating]);
            });
        }

        // Geo-distance filter (haversine) — requires lat, lng, radius_km
        if ($request->filled('lat') && $request->filled('lng')) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $radius = (float) ($request->radius_km ?? 25);

            $haversine = "(6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude))))";

            $showrooms->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereRaw("$haversine < ?", [$radius]);

            $selectCols[] = \DB::raw("$haversine AS distance");
            $showrooms->orderByRaw("$haversine ASC");
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

        $carPrice = $request->car_price;
        $downPayment = $request->down_payment;
        $tenureMonths = $request->tenure_months ?? 36;
        $interestRate = ($request->interest_rate ?? 10) / 100; // Convert to decimal

        if ($downPayment >= $carPrice) {
            return response()->json([
                'message' => trans('translate.Down payment cannot be greater than car price')
            ], 403);
        }

        $loanAmount = $carPrice - $downPayment;

        // Calculate monthly installment
        $monthlyRate = $interestRate / 12;
        $installment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $tenureMonths)) / 
                      (pow(1 + $monthlyRate, $tenureMonths) - 1);

        return response()->json([
            'car_price' => $carPrice,
            'down_payment' => $downPayment,
            'loan_amount' => round($loanAmount, 2),
            'monthly_installment' => round($installment, 2),
            'tenure_months' => $tenureMonths,
            'interest_rate' => $interestRate * 100,
            'total_payment' => round($downPayment + ($installment * $tenureMonths), 2),
        ]);
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
        ];

        $this->validate($request, $rules);

        $car = Car::findOrFail($request->car_id);

        // Determine showroom_id
        $showroom_id = $request->showroom_id ?? $car->agent_id;

        // Create booking/application
        $application = new Booking();
        $application->order_id = substr(rand(0, time()), 0, 10);
        $application->user_id = $user->id;
        $application->supplier_id = $car->agent_id;
        $application->car_id = $car->id;
        $application->showroom_id = $showroom_id;
        $application->application_type = Booking::APPLICATION_TYPE_LEASING;
        $application->down_payment = $request->down_payment;
        $application->installment_amount = $request->installment_amount;
        $application->price = $car->regular_price;
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

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
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
     * Pay DP (Down Payment)
     * POST /api/applications/{id}/pay-dp
     */
    public function payDP(Request $request, $id)
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

        // Check if application is approved
        if ($application->leasing_status != Booking::LEASING_STATUS_APPROVED) {
            return response()->json([
                'message' => trans('translate.Application must be approved before paying DP')
            ], 403);
        }

        // TODO: Integrate with payment gateway
        // For now, just mark as paid (actual implementation depends on payment flow)

        return response()->json([
            'message' => trans('translate.DP payment initiated'),
            'application' => $application,
        ]);
    }
}




