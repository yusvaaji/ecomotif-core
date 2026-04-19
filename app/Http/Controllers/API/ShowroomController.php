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

        if ($user->is_dealer != 1 && !$user->isMarketing()) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can access applications')
            ], 403);
        }

        $showroom_id = $user->is_dealer == 1 ? $user->id : $user->showroom_id;

        $applications = Booking::with('car.brand', 'consumer', 'mediator', 'marketing')
            ->where(function($query) use ($showroom_id) {
                $query->where('showroom_id', $showroom_id)
                    ->orWhere('supplier_id', $showroom_id);
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
     * Claim application by marketing/sales
     * POST /api/user/showroom/applications/{id}/claim
     */
    public function claimApplication(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        if (!$user->isMarketing()) {
            return response()->json([
                'message' => trans('translate.Only marketing/sales can claim applications')
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

        if ($application->marketing_id != null) {
            if ($application->marketing_id == $user->id) {
                return response()->json([
                    'message' => trans('translate.You have already claimed this application')
                ], 400);
            }
            return response()->json([
                'message' => trans('translate.Application has already been claimed by another sales')
            ], 403);
        }

        $application->marketing_id = $user->id;
        $application->save();

        return response()->json([
            'message' => trans('translate.Application claimed successfully'),
            'application' => $application,
        ]);
    }

    /**
     * Get application details
     * GET /api/user/showroom/applications/{id}
     */
    public function applicationDetails($id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer != 1 && !$user->isMarketing()) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can access application details')
            ], 403);
        }

        $showroom_id = $user->is_dealer == 1 ? $user->id : $user->showroom_id;

        $application = Booking::with('car.brand', 'consumer', 'mediator', 'marketing', 'showroom')
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

    /**
     * Reject Application
     * POST /api/user/showroom/applications/{id}/reject
     */
    public function rejectApplication(Request $request, $id)
    {
        $user = Auth::guard('api')->user();

        if ($user->is_dealer != 1) {
            return response()->json([
                'message' => trans('translate.Only dealer/showroom can reject applications')
            ], 403);
        }

        $application = Booking::where(function($query) use ($user) {
            $query->where('showroom_id', $user->id)
                ->orWhere('supplier_id', $user->id);
        })->where('id', $id)->first();

        if (!$application) {
            return response()->json([
                'message' => trans('translate.Application not found')
            ], 404);
        }

        if ($application->status == Booking::STATUS_COMPLETED || $application->status == Booking::STATUS_CANCELLED_BY_USER || $application->status == Booking::STATUS_CANCELLED_BY_DEALER) {
            return response()->json([
                'message' => trans('translate.Cannot reject this application')
            ], 403);
        }

        $application->status = Booking::STATUS_CANCELLED_BY_DEALER;
        $application->save();

        return response()->json([
            'message' => trans('translate.Application rejected successfully'),
            'application' => $application,
        ]);
    }

    /**
     * Get Sales Performance for Showroom
     * GET /api/user/showroom/performance
     */
    public function performance(Request $request)
    {
        $user = auth()->user();
        if ($user->is_dealer != 1 && !$user->isMarketing()) {
            return response()->json(['message' => trans('translate.Unauthorized')], 403);
        }

        $showroom_id = $user->is_dealer == 1 ? $user->id : $user->showroom_id;

        $query = \App\Models\Booking::where('showroom_id', $showroom_id);
        
        if ($user->is_dealer == 0 && $user->isMarketing()) {
            $query->where('marketing_id', $user->id);
        }

        $total_orders = (clone $query)->count();
        $successful_orders = (clone $query)->where('status', \App\Models\Booking::STATUS_COMPLETED)->count();
        
        $pending_orders = (clone $query)->whereIn('status', [
            \App\Models\Booking::STATUS_PENDING, 
            \App\Models\Booking::STATUS_CONTACTED,
            \App\Models\Booking::STATUS_APPROVED
        ])->count();
        
        $cancelled_orders = (clone $query)->whereIn('status', [
            \App\Models\Booking::STATUS_CANCELLED_BY_USER, 
            \App\Models\Booking::STATUS_CANCELLED_BY_DEALER
        ])->count();
        
        $total_revenue = (clone $query)->where('status', \App\Models\Booking::STATUS_COMPLETED)->sum('price');
        
        $top_marketing = [];
        if ($user->is_dealer == 1) {
            $top_marketing = \App\Models\User::where('showroom_id', $showroom_id)
                ->where('is_dealer', 0)
                ->withCount(['marketingApplications as successful_orders' => function ($q) {
                    $q->where('status', \App\Models\Booking::STATUS_COMPLETED);
                }])
                ->withSum(['marketingApplications as total_revenue' => function ($q) {
                    $q->where('status', \App\Models\Booking::STATUS_COMPLETED);
                }], 'price')
                ->orderByDesc('total_revenue')
                ->get();
        }

        return response()->json([
            'total_orders' => $total_orders,
            'successful_orders' => $successful_orders,
            'pending_orders' => $pending_orders,
            'cancelled_orders' => $cancelled_orders,
            'total_revenue' => $total_revenue,
            'marketing_performance' => $top_marketing
        ]);
    }

    /**
     * Get Marketing Users for this Showroom
     * GET /api/user/showroom/marketing
     */
    public function getMarketingUsers()
    {
        $user = Auth::guard('api')->user();
        if ($user->is_dealer != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $marketingUsers = User::where('showroom_id', $user->id)
            ->where('is_dealer', 0)
            ->where('is_mediator', 0)
            ->get();

        return response()->json(['marketing_users' => $marketingUsers]);
    }

    /**
     * Add Marketing User to this Showroom
     * POST /api/user/showroom/marketing
     */
    public function addMarketingUser(Request $request)
    {
        $user = Auth::guard('api')->user();
        if ($user->is_dealer != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        $marketingUser = new User();
        $marketingUser->name = $request->name;
        $marketingUser->username = explode('@', $request->email)[0] . rand(1000, 9999);
        $marketingUser->email = $request->email;
        $marketingUser->phone = $request->phone;
        $marketingUser->password = bcrypt($request->password);
        $marketingUser->showroom_id = $user->id;
        $marketingUser->is_dealer = 0;
        $marketingUser->is_mediator = 0;
        $marketingUser->status = User::STATUS_ACTIVE;
        $marketingUser->email_verified_at = now(); // Skip verification
        $marketingUser->save();

        return response()->json([
            'message' => 'Sales berhasil ditambahkan', 
            'marketing_user' => $marketingUser
        ]);
    }

    /**
     * Remove Marketing User from this Showroom
     * DELETE /api/user/showroom/marketing/{id}
     */
    public function removeMarketingUser($id)
    {
        $user = Auth::guard('api')->user();
        if ($user->is_dealer != 1) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $marketingUser = User::where('showroom_id', $user->id)->where('id', $id)->first();
        if (!$marketingUser) {
            return response()->json(['message' => 'Sales tidak ditemukan di showroom ini'], 404);
        }

        $marketingUser->showroom_id = null;
        $marketingUser->save();

        return response()->json(['message' => 'Sales berhasil dihapus dari showroom']);
    }
}
