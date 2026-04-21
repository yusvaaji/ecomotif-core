<?php

namespace App\Services;

use InvalidArgumentException;

class LeasingQuoteService
{
    /**
     * @return array{
     *   car_price: float,
     *   down_payment: float,
     *   loan_amount: float,
     *   monthly_installment: float,
     *   tenure_months: int,
     *   interest_rate: float,
     *   total_payment: float
     * }
     */
    public function quote(
        float $carPrice,
        float $downPayment,
        int $tenureMonths = 36,
        float $interestRatePercent = 10.0
    ): array {
        if ($downPayment >= $carPrice) {
            throw new InvalidArgumentException(trans('translate.Down payment cannot be greater than car price'));
        }

        $interestRate = $interestRatePercent / 100;
        $loanAmount = $carPrice - $downPayment;

        $monthlyRate = $interestRate / 12;
        $installment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $tenureMonths))
            / (pow(1 + $monthlyRate, $tenureMonths) - 1);

        return [
            'car_price' => $carPrice,
            'down_payment' => $downPayment,
            'loan_amount' => round($loanAmount, 2),
            'monthly_installment' => round($installment, 2),
            'tenure_months' => $tenureMonths,
            'interest_rate' => $interestRatePercent,
            'total_payment' => round($downPayment + ($installment * $tenureMonths), 2),
        ];
    }
}
