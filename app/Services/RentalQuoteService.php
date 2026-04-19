<?php

namespace App\Services;

use Carbon\Carbon;
use InvalidArgumentException;
use Modules\Car\Entities\Car;
use Modules\GeneralSetting\Entities\Setting;

class RentalQuoteService
{
    /**
     * @return array{
     *   sub_total: float,
     *   number_of_days: int,
     *   vat_percentage: float|int|string|null,
     *   platform_percentage: float|int|string|null,
     *   vat_amount: float,
     *   platform_fee_amount: float,
     *   grand_total: float
     * }
     */
    public function quote(Car $car, string $pickupDateYmd, string $returnDateYmd): array
    {
        $pickupDate = Carbon::createFromFormat('Y-m-d', $pickupDateYmd);
        $returnDate = Carbon::createFromFormat('Y-m-d', $returnDateYmd);

        if ($returnDate->lt($pickupDate)) {
            throw new InvalidArgumentException(trans('translate.Return date must be on or after pickup date'));
        }

        $numberOfDays = $returnDate->diffInDays($pickupDate) + 1;

        $subTotal = (float) $car->regular_price * $numberOfDays;

        $generalSetting = Setting::first();
        $vatPercentage = (float) ($generalSetting->vat_percentage ?? 0);
        $platformPercentage = (float) ($generalSetting->platform_percentage ?? 0);

        $vatAmount = ($subTotal * $vatPercentage) / 100;
        $platformFeeAmount = ($subTotal * $platformPercentage) / 100;
        $grandTotal = $subTotal + $vatAmount + $platformFeeAmount;

        return [
            'sub_total' => round($subTotal, 2),
            'number_of_days' => $numberOfDays,
            'vat_percentage' => $generalSetting->vat_percentage ?? 0,
            'platform_percentage' => $generalSetting->platform_percentage ?? 0,
            'vat_amount' => round($vatAmount, 2),
            'platform_fee_amount' => round($platformFeeAmount, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }
}
