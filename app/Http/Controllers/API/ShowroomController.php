<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Booking;
use Modules\Car\Entities\Car;
use App\Services\LeasingService;
use Illuminate\Support\Str;

class ShowroomController extends Controller
{
    protected $leasingService;

    public function __construct(LeasingService $leasingService)
    {
        $this->leasingService = $leasingService;
    }
    /**
     * Generate barcode/QR code for showroom
     * POST /api/user/showroom/generate-barcode
     */
    public function generateBarcode(Request $request)
    {
        $user = Auth::guard('api')->user();

        // Check if user is dealer/showroom
        if ($user->is_dealer != 1) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can generate barcode')
            ], 403);
        }

        // Generate unique barcode
        $barcode = 'SHOWROOM-' . strtoupper(Str::random(10)) . '-' . $user->id;
        
        // Generate QR code (same as barcode for now, can be enhanced with QR library)
        $qrCode = $barcode;

        // Update user barcode
        $user->barcode = $barcode;
        $user->save();

        return response()->json([
            'message' => trans('translate.Barcode generated successfully'),
            'barcode' => $barcode,
            'qr_code' => $qrCode,
        ]);
    }

    /**
     * Get barcode for showroom
     * GET /api/user/showroom/barcode
     */
    public function getBarcode(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer != 1) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can access barcode')
            ], 403);
        }

        return response()->json([
            'barcode' => $user->barcode,
            'showroom_id' => $user->id,
            'showroom_name' => $user->name,
        ]);
    }

    /**
     * Scan barcode and get showroom info
     * POST /api/scan-showroom/{code}
     * Public endpoint
     */
    public function scanBarcode($code)
    {
        $showroom = User::where('barcode', $code)
            ->where('is_dealer', 1)
            ->where('status', 'enable')
            ->where('is_banned', 'no')
            ->where('email_verified_at', '!=', null)
            ->first();

        if (!$showroom) {
            return response()->json([
                'message' => trans('translate.Showroom not found or inactive')
            ], 404);
        }

        return response()->json([
            'showroom' => [
                'id' => $showroom->id,
                'name' => $showroom->name,
                'username' => $showroom->username,
                'image' => $showroom->image,
                'address' => $showroom->address,
                'phone' => $showroom->phone,
                'email' => $showroom->email,
                'designation' => $showroom->designation,
            ],
        ]);
    }

    /**
     * List applications for showroom
     * GET /api/user/showroom/applications
     */
    public function applications(Request $request)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer != 1) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can access applications')
            ], 403);
        }

        $applications = Booking::with('car', 'consumer', 'mediator', 'marketing')
            ->where(function($query) use ($user) {
                $query->where('showroom_id', $user->id)
                    ->orWhere('supplier_id', $user->id);
            });

        // Filter by status
        if ($request->status) {
            $applications->where('status', $request->status);
        }

        // Filter by leasing_status
        if ($request->leasing_status) {
            $applications->where('leasing_status', $request->leasing_status);
        }

        // Filter by application_type
        if ($request->application_type) {
            $applications->where('application_type', $request->application_type);
        }

        // Filter by source
        if ($request->source) {
            if ($request->source == 'direct') {
                $applications->whereNull('mediator_id')->whereNull('marketing_id');
            } elseif ($request->source == 'mediator') {
                $applications->whereNotNull('mediator_id');
            } elseif ($request->source == 'marketing') {
                $applications->whereNotNull('marketing_id');
            }
        }

        $applications = $applications->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'applications' => $applications,
        ]);
    }

    /**
     * Get application details
     * GET /api/user/showroom/applications/{id}
     */
    public function applicationDetails($id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer != 1) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can access application details')
            ], 403);
        }

        $application = Booking::with('car', 'consumer', 'mediator', 'marketing', 'showroom')
            ->where(function($query) use ($user, $id) {
                $query->where('showroom_id', $user->id)
                    ->orWhere('supplier_id', $user->id);
            })
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
     * Review application
     * POST /api/user/showroom/applications/{id}/review
     */
    public function reviewApplication(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer != 1) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can review applications')
            ], 403);
        }

        $application = Booking::where(function($query) use ($user, $id) {
            $query->where('showroom_id', $user->id)
                ->orWhere('supplier_id', $user->id);
        })->where('id', $id)->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        // Set showroom_id if not set
        if (!$application->showroom_id) {
            $application->showroom_id = $user->id;
        }

        $application->save();

        return response()->json([
            'message' => trans('translate.Application reviewed successfully'),
            'application' => $application,
        ]);
    }

    /**
     * Pool application to leasing
     * POST /api/user/showroom/applications/{id}/pool-to-leasing
     */
    public function poolToLeasing(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer != 1) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can pool to leasing')
            ], 403);
        }

        $application = Booking::where(function($query) use ($user, $id) {
            $query->where('showroom_id', $user->id)
                ->orWhere('supplier_id', $user->id);
        })->where('id', $id)->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        // Use LeasingService to submit to leasing
        $result = $this->leasingService->submitApplication($application->id);

        if ($result['success']) {
            $application->refresh();
            return response()->json([
                'message' => trans('translate.Application pooled to leasing successfully'),
                'application' => $application,
                'leasing_reference' => $result['leasing_reference'] ?? null,
            ]);
        } else {
            return response()->json([
                'message' => $result['message'] ?? trans('translate.Error pooling application to leasing'),
            ], 500);
        }
    }

    /**
     * Receive leasing result
     * GET /api/user/showroom/applications/{id}/leasing-result
     */
    public function receiveLeasingResult($id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer != 1) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can access leasing results')
            ], 403);
        }

        $application = Booking::where(function($query) use ($user, $id) {
            $query->where('showroom_id', $user->id)
                ->orWhere('supplier_id', $user->id);
        })->where('id', $id)->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        return response()->json([
            'application' => $application,
            'leasing_status' => $application->leasing_status,
            'leasing_notes' => $application->leasing_notes,
        ]);
    }

    /**
     * Appeal to leasing
     * POST /api/user/showroom/applications/{id}/appeal
     */
    public function appealToLeasing(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer != 1) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can appeal to leasing')
            ], 403);
        }

        $rules = [
            'reason' => 'required|string|max:500',
        ];

        $this->validate($request, $rules);

        $application = Booking::where(function($query) use ($user, $id) {
            $query->where('showroom_id', $user->id)
                ->orWhere('supplier_id', $user->id);
        })->where('id', $id)->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        if (!$application->canAppeal()) {
            return response()->json([
                'message' => trans('translate.Application cannot be appealed')
            ], 403);
        }

        // Use LeasingService to submit appeal
        $result = $this->leasingService->submitAppeal($application->id, $request->reason);

        if ($result['success']) {
            $application->refresh();
            return response()->json([
                'message' => trans('translate.Appeal submitted successfully'),
                'application' => $application,
            ]);
        } else {
            return response()->json([
                'message' => $result['message'] ?? trans('translate.Error submitting appeal'),
            ], 500);
        }
    }

    /**
     * Handle DP (Down Payment)
     * POST /api/user/showroom/applications/{id}/handle-dp
     */
    public function handleDP(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer != 1) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can handle DP')
            ], 403);
        }

        $application = Booking::where(function($query) use ($user, $id) {
            $query->where('showroom_id', $user->id)
                ->orWhere('supplier_id', $user->id);
        })->where('id', $id)->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        // Mark DP as received and forward to leasing
        // This is a placeholder - actual implementation depends on payment flow

        return response()->json([
            'message' => trans('translate.DP handled successfully'),
            'application' => $application,
        ]);
    }
}

