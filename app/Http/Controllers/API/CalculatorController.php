<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PaymentCapabilityCalculator;

class CalculatorController extends Controller
{
    protected $calculator;

    public function __construct(PaymentCapabilityCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Calculate payment capability
     * 
     * POST /api/calculator/payment-capability
     * 
     * Request body:
     * {
     *   "monthly_income": 10000000,
     *   "monthly_expenses": 5000000,
     *   "existing_loans": 2000000,
     *   "car_price": 300000000,
     *   "tenure_months": 36
     * }
     */
    public function paymentCapability(Request $request)
    {
        $rules = [
            'monthly_income' => 'required|numeric|min:0',
            'monthly_expenses' => 'required|numeric|min:0',
            'existing_loans' => 'required|numeric|min:0',
            'car_price' => 'required|numeric|min:0',
            'tenure_months' => 'nullable|integer|min:12|max:60',
        ];

        $customMessages = [
            'monthly_income.required' => trans('translate.Monthly income is required'),
            'monthly_expenses.required' => trans('translate.Monthly expenses is required'),
            'existing_loans.required' => trans('translate.Existing loans is required'),
            'car_price.required' => trans('translate.Car price is required'),
        ];

        $this->validate($request, $rules, $customMessages);

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
}




