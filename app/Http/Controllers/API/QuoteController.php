<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\GarageService;
use App\Services\LeasingQuoteService;
use App\Services\RentalQuoteService;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Modules\Car\Entities\Car;

class QuoteController extends Controller
{
    public function __construct(
        protected RentalQuoteService $rentalQuoteService,
        protected LeasingQuoteService $leasingQuoteService
    ) {
    }

    /**
     * POST /api/quotes/rental — public price estimate (no auth).
     */
    public function rental(Request $request)
    {
        $request->validate([
            'car_id' => 'required|integer',
            'pickup_location' => 'required|exists:cities,id',
            'return_location' => 'required|exists:cities,id',
            'pickup_date' => 'required|date_format:Y-m-d',
            'return_date' => 'required|date_format:Y-m-d',
        ]);

        $car = Car::where('id', $request->car_id)->first();
        if (! $car) {
            return response()->json([
                'message' => trans('translate.Listing Not Found!'),
            ], 403);
        }

        try {
            $breakdown = $this->rentalQuoteService->quote(
                $car,
                $request->pickup_date,
                $request->return_date
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(array_merge($breakdown, [
            'car_id' => (int) $car->id,
            'pickup_location' => (int) $request->pickup_location,
            'return_location' => (int) $request->return_location,
            'pickup_date' => $request->pickup_date,
            'return_date' => $request->return_date,
            'vat_mount' => $breakdown['vat_amount'],
        ]));
    }

    /**
     * POST /api/quotes/leasing — same math as /api/calculate-installment.
     */
    public function leasing(Request $request)
    {
        $request->validate([
            'car_price' => 'required|numeric|min:0',
            'down_payment' => 'required|numeric|min:0',
            'tenure_months' => 'nullable|integer|min:12|max:60',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            $data = $this->leasingQuoteService->quote(
                (float) $request->car_price,
                (float) $request->down_payment,
                (int) ($request->tenure_months ?? 36),
                (float) ($request->interest_rate ?? 10)
            );
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        return response()->json($data);
    }

    /**
     * POST /api/quotes/garage — sum active services by id (same garage).
     */
    public function garage(Request $request)
    {
        $request->validate([
            'garage_id' => 'required|integer|exists:users,id',
            'garage_service_ids' => 'required|array|min:1',
            'garage_service_ids.*' => 'integer|exists:garage_services,id',
        ]);

        $ids = array_unique($request->garage_service_ids);

        $services = GarageService::query()
            ->where('garage_id', $request->garage_id)
            ->where('status', 'active')
            ->whereIn('id', $ids)
            ->get();

        if ($services->count() !== count($ids)) {
            return response()->json([
                'message' => trans('translate.One or more services are invalid for this garage'),
            ], 422);
        }

        $lineItems = $services->map(fn (GarageService $s) => [
            'id' => $s->id,
            'name' => $s->name,
            'price' => (float) $s->price,
        ]);

        $subTotal = (float) $services->sum('price');

        return response()->json([
            'garage_id' => (int) $request->garage_id,
            'line_items' => $lineItems,
            'sub_total' => round($subTotal, 2),
            'grand_total' => round($subTotal, 2),
        ]);
    }
}
