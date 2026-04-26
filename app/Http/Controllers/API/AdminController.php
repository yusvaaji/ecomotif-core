<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Car\Entities\Car;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Get pending cars that need verification
     */
    public function pending_cars(Request $request)
    {
        // Admin middleware / guard should protect this route
        
        $cars = Car::with([
            'dealer' => function ($query) {
                $query->select('id', 'name', 'email', 'image', 'address', 'partner_id'); // partner_id needed for showroom relation
            },
            'dealer.showroom' => function ($query) {
                $query->select('id', 'name', 'address');
            },
            'brand',
            'galleries'
        ])
            ->orderByRaw("FIELD(approved_by_admin, 'pending') DESC") // Prioritaskan pending di atas
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
        // Get users who are either dealer or garage
        $mitra = \App\Models\User::with(['merchantProfile.subscriptionPlan'])
            ->where('is_dealer', 1)
            ->orWhere('is_garage', 1)
            ->orderBy('id', 'desc')
            ->paginate(30);

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
