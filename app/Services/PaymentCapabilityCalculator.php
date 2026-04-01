<?php

namespace App\Services;

class PaymentCapabilityCalculator
{
    /**
     * Default DSR (Debt Service Ratio) - maksimal 40% dari disposable income
     */
    const DEFAULT_DSR_PERCENTAGE = 0.40;

    /**
     * Minimum DP percentage
     */
    const MIN_DP_PERCENTAGE = 0.10; // 10%

    /**
     * Maximum DP percentage
     */
    const MAX_DP_PERCENTAGE = 0.50; // 50%

    /**
     * Calculate payment capability for consumer
     * 
     * @param float $monthlyIncome Monthly income
     * @param float $monthlyExpenses Monthly expenses (excluding existing loans)
     * @param float $existingLoansMonthly Existing monthly loan payments
     * @param float $carPrice Car price
     * @param int $tenureMonths Loan tenure in months (default: 36)
     * @return array
     */
    public function calculate(
        float $monthlyIncome,
        float $monthlyExpenses,
        float $existingLoansMonthly,
        float $carPrice,
        int $tenureMonths = 36
    ): array {
        // Calculate disposable income
        $disposableIncome = $monthlyIncome - $monthlyExpenses - $existingLoansMonthly;

        // Calculate maximum monthly payment based on DSR
        $maxMonthlyPayment = $disposableIncome * self::DEFAULT_DSR_PERCENTAGE;

        // Calculate recommended DP percentage
        $recommendedDPPercentage = $this->calculateRecommendedDP($disposableIncome, $carPrice, $maxMonthlyPayment, $tenureMonths);

        // Calculate DP amount
        $recommendedDP = $carPrice * $recommendedDPPercentage;

        // Calculate loan amount
        $loanAmount = $carPrice - $recommendedDP;

        // Calculate monthly installment
        $monthlyInstallment = $this->calculateInstallment($loanAmount, $tenureMonths);

        // Check if installment is within capability
        $isAffordable = $monthlyInstallment <= $maxMonthlyPayment;

        // If not affordable, adjust DP
        if (!$isAffordable) {
            $adjustedDP = $this->adjustDPForAffordability($carPrice, $maxMonthlyPayment, $tenureMonths);
            $adjustedLoanAmount = $carPrice - $adjustedDP;
            $adjustedInstallment = $this->calculateInstallment($adjustedLoanAmount, $tenureMonths);
            
            return [
                'affordable' => true,
                'monthly_income' => $monthlyIncome,
                'monthly_expenses' => $monthlyExpenses,
                'existing_loans' => $existingLoansMonthly,
                'disposable_income' => round($disposableIncome, 2),
                'max_monthly_payment' => round($maxMonthlyPayment, 2),
                'car_price' => $carPrice,
                'recommended_dp_percentage' => round(($adjustedDP / $carPrice) * 100, 2),
                'recommended_dp' => round($adjustedDP, 2),
                'loan_amount' => round($adjustedLoanAmount, 2),
                'monthly_installment' => round($adjustedInstallment, 2),
                'tenure_months' => $tenureMonths,
                'adjusted' => true,
                'message' => 'DP adjusted to make installment affordable',
            ];
        }

        return [
            'affordable' => true,
            'monthly_income' => $monthlyIncome,
            'monthly_expenses' => $monthlyExpenses,
            'existing_loans' => $existingLoansMonthly,
            'disposable_income' => round($disposableIncome, 2),
            'max_monthly_payment' => round($maxMonthlyPayment, 2),
            'car_price' => $carPrice,
            'recommended_dp_percentage' => round($recommendedDPPercentage * 100, 2),
            'recommended_dp' => round($recommendedDP, 2),
            'loan_amount' => round($loanAmount, 2),
            'monthly_installment' => round($monthlyInstallment, 2),
            'tenure_months' => $tenureMonths,
            'adjusted' => false,
            'message' => 'Installment is within payment capability',
        ];
    }

    /**
     * Calculate recommended DP percentage
     */
    private function calculateRecommendedDP(
        float $disposableIncome,
        float $carPrice,
        float $maxMonthlyPayment,
        int $tenureMonths
    ): float {
        // Start with minimum DP
        $dpPercentage = self::MIN_DP_PERCENTAGE;
        
        // Try to find optimal DP that makes installment affordable
        for ($dp = self::MIN_DP_PERCENTAGE; $dp <= self::MAX_DP_PERCENTAGE; $dp += 0.05) {
            $dpAmount = $carPrice * $dp;
            $loanAmount = $carPrice - $dpAmount;
            $installment = $this->calculateInstallment($loanAmount, $tenureMonths);
            
            if ($installment <= $maxMonthlyPayment) {
                $dpPercentage = $dp;
                break;
            }
        }
        
        return $dpPercentage;
    }

    /**
     * Adjust DP to make installment affordable
     */
    private function adjustDPForAffordability(
        float $carPrice,
        float $maxMonthlyPayment,
        int $tenureMonths
    ): float {
        // Calculate maximum loan amount based on max monthly payment
        $maxLoanAmount = $this->calculateMaxLoanAmount($maxMonthlyPayment, $tenureMonths);
        
        // Calculate required DP
        $requiredDP = $carPrice - $maxLoanAmount;
        
        // Ensure DP is at least minimum
        if ($requiredDP < ($carPrice * self::MIN_DP_PERCENTAGE)) {
            $requiredDP = $carPrice * self::MIN_DP_PERCENTAGE;
        }
        
        return $requiredDP;
    }

    /**
     * Calculate maximum loan amount based on monthly payment
     */
    private function calculateMaxLoanAmount(float $monthlyPayment, int $tenureMonths): float
    {
        // Simple calculation: monthly payment * tenure
        // In real scenario, this should use interest rate formula
        // For now, using simplified calculation
        $interestRate = 0.10; // 10% annual interest rate (adjustable)
        $monthlyRate = $interestRate / 12;
        
        if ($monthlyRate > 0) {
            $maxLoanAmount = $monthlyPayment * ((1 - pow(1 + $monthlyRate, -$tenureMonths)) / $monthlyRate);
        } else {
            $maxLoanAmount = $monthlyPayment * $tenureMonths;
        }
        
        return $maxLoanAmount;
    }

    /**
     * Calculate monthly installment
     * 
     * @param float $loanAmount
     * @param int $tenureMonths
     * @param float $interestRate Annual interest rate (default: 10%)
     * @return float
     */
    private function calculateInstallment(
        float $loanAmount,
        int $tenureMonths,
        float $interestRate = 0.10
    ): float {
        if ($loanAmount <= 0 || $tenureMonths <= 0) {
            return 0;
        }

        $monthlyRate = $interestRate / 12;
        
        if ($monthlyRate > 0) {
            // Formula: PMT = P * (r(1+r)^n) / ((1+r)^n - 1)
            $installment = $loanAmount * ($monthlyRate * pow(1 + $monthlyRate, $tenureMonths)) / 
                          (pow(1 + $monthlyRate, $tenureMonths) - 1);
        } else {
            // No interest
            $installment = $loanAmount / $tenureMonths;
        }
        
        return $installment;
    }
}




