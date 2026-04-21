<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Subscription\Entities\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            // Showroom Baru
            [
                'plan_name' => 'Rintis',
                'plan_price' => 1000000.00,
                'plan_type' => 'showroom_baru',
                'max_car' => 10,
                'max_user' => 1,
                'expiration_date' => 'monthly',
                'featured_car' => 0,
                'status' => 'active',
                'serial' => 1,
            ],
            [
                'plan_name' => 'Tumbuh',
                'plan_price' => 1500000.00,
                'plan_type' => 'showroom_baru',
                'max_car' => 15,
                'max_user' => 2,
                'expiration_date' => 'monthly',
                'featured_car' => 0,
                'status' => 'active',
                'serial' => 2,
            ],
            [
                'plan_name' => 'Mandiri',
                'plan_price' => 2000000.00,
                'plan_type' => 'showroom_baru',
                'max_car' => 20,
                'max_user' => 3,
                'expiration_date' => 'monthly',
                'featured_car' => 0,
                'status' => 'active',
                'serial' => 3,
            ],

            // Showroom Lama
            [
                'plan_name' => 'Rintis',
                'plan_price' => 500000.00,
                'plan_type' => 'showroom_lama',
                'max_car' => 10,
                'max_user' => 1,
                'expiration_date' => 'monthly',
                'featured_car' => 0,
                'status' => 'active',
                'serial' => 4,
            ],
            [
                'plan_name' => 'Tumbuh',
                'plan_price' => 750000.00,
                'plan_type' => 'showroom_lama',
                'max_car' => 15,
                'max_user' => 2,
                'expiration_date' => 'monthly',
                'featured_car' => 0,
                'status' => 'active',
                'serial' => 5,
            ],
            [
                'plan_name' => 'Mandiri',
                'plan_price' => 1000000.00,
                'plan_type' => 'showroom_lama',
                'max_car' => 20,
                'max_user' => 3,
                'expiration_date' => 'monthly',
                'featured_car' => 0,
                'status' => 'active',
                'serial' => 6,
            ],

            // Bengkel
            [
                'plan_name' => 'Rintis',
                'plan_price' => 500000.00, // Harga dummy, adjust if needed
                'plan_type' => 'bengkel',
                'max_car' => 0,
                'max_user' => 1, // 1 Montir
                'expiration_date' => 'monthly',
                'featured_car' => 0,
                'status' => 'active',
                'serial' => 7,
            ],
            [
                'plan_name' => 'Tumbuh',
                'plan_price' => 1000000.00, // Harga dummy
                'plan_type' => 'bengkel',
                'max_car' => 0,
                'max_user' => 5, // 5 Montir
                'expiration_date' => 'monthly',
                'featured_car' => 0,
                'status' => 'active',
                'serial' => 8,
            ],
            [
                'plan_name' => 'Mandiri',
                'plan_price' => 2000000.00, // Harga dummy
                'plan_type' => 'bengkel',
                'max_car' => 0,
                'max_user' => 10, // 10 Montir
                'expiration_date' => 'monthly',
                'featured_car' => 0,
                'status' => 'active',
                'serial' => 9,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::create($plan);
        }
    }
}
