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
        
        // Count Cars (you may import App\Models\Car at the top or use full path)
        $totalUnit = \App\Models\Car::count();
        
        $totalUser = \App\Models\User::where('is_dealer', 0)
            ->where('is_garage', 0)
            ->where('is_admin', 0)
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
