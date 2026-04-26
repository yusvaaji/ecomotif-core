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
        
        $cars = Car::with(['dealer', 'dealer.showroom', 'brand'])
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
}
