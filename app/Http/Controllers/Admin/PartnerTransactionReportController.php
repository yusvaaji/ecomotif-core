<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ServiceBooking;
use App\Models\User;
use Illuminate\Http\Request;
use Modules\Subscription\Entities\SubscriptionHistory;

class PartnerTransactionReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * JSON report for owner: bookings per dealer or service bookings per garage.
     */
    public function index(Request $request)
    {
        $request->validate([
            'partner_type' => 'required|in:dealer,garage',
            'partner_id' => 'required|integer|exists:users,id',
            'status' => 'nullable|string|max:50',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        $partner = User::findOrFail($request->partner_id);
        $perPage = (int) ($request->input('per_page', 25));

        if ($request->partner_type === 'dealer' && ! (int) $partner->is_dealer) {
            return response()->json(['message' => 'Invalid dealer partner'], 422);
        }
        if ($request->partner_type === 'garage' && ! (int) $partner->is_garage) {
            return response()->json(['message' => 'Invalid garage partner'], 422);
        }

        $payload = [
            'partner' => [
                'id' => $partner->id,
                'name' => $partner->name,
                'email' => $partner->email,
                'type' => $request->partner_type,
                'created_at' => $partner->created_at,
            ],
        ];

        if ($request->partner_type === 'dealer') {
            $q = Booking::query()->where('supplier_id', $partner->id)->with(['consumer', 'car']);
            if ($request->filled('status')) {
                $q->where('status', $request->status);
            }
            if ($request->filled('date_from')) {
                $q->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $q->whereDate('created_at', '<=', $request->date_to);
            }
            $payload['bookings'] = $q->orderByDesc('id')->paginate($perPage);
            $payload['subscription_histories'] = SubscriptionHistory::where('user_id', $partner->id)
                ->orderByDesc('id')
                ->limit(50)
                ->get();
        } else {
            $q = ServiceBooking::query()->where('garage_id', $partner->id)->with(['service', 'customer']);
            if ($request->filled('status')) {
                $q->where('status', $request->status);
            }
            if ($request->filled('date_from')) {
                $q->whereDate('created_at', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $q->whereDate('created_at', '<=', $request->date_to);
            }
            $payload['service_bookings'] = $q->orderByDesc('id')->paginate($perPage);
        }

        return response()->json($payload);
    }
}
