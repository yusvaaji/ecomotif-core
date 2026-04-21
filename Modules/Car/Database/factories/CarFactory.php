<?php

namespace Modules\Car\Database\factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Modules\Car\Entities\Car;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    protected $model = Car::class;

    public function definition(): array
    {
        $brandId = DB::table('brands')->insertGetId([
            'image' => 'default.png',
            'slug' => 'brand-'.uniqid(),
            'status' => 'enable',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cityId = DB::table('cities')->insertGetId([
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [
            'agent_id' => User::factory(),
            'brand_id' => $brandId,
            'city_id' => $cityId,
            'thumb_image' => 'thumb.jpg',
            'slug' => 'car-'.uniqid(),
            'condition' => 'used',
            'vehicle_type' => 'car',
            'regular_price' => 100000000,
            'offer_price' => null,
            'status' => 'enable',
            'approved_by_admin' => 'approved',
        ];
    }
}
