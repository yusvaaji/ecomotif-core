<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Car\Entities\Car;

/**
 * @extends Factory<Booking>
 */
class BookingFactory extends Factory
{
    protected $model = Booking::class;

    public function definition(): array
    {
        $car = Car::factory()->create();

        return [
            'order_id' => 'O-'.strtoupper(substr(uniqid(), 0, 10)),
            'user_id' => User::factory(),
            'supplier_id' => $car->agent_id,
            'car_id' => $car->id,
            'price' => 1000000,
            'total_price' => 1100000,
            'vat_amount' => 0,
            'platform_amount' => 0,
            'pickup_location' => $car->city_id,
            'return_location' => $car->city_id,
            'pickup_date' => now()->addDay()->toDateString(),
            'pickup_time' => '10:00',
            'return_date' => now()->addDays(3)->toDateString(),
            'return_time' => '10:00',
            'duration' => 3,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'transaction' => null,
            'booking_note' => null,
            'status' => Booking::STATUS_PENDING,
            'application_type' => Booking::APPLICATION_TYPE_LEASING,
            'down_payment' => 0,
            'installment_amount' => 0,
            'leasing_status' => Booking::LEASING_STATUS_PENDING,
        ];
    }
}
