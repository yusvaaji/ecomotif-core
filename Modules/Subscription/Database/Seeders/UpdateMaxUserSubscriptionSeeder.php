<?php

namespace Modules\Subscription\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Subscription\Entities\SubscriptionPlan;

class UpdateMaxUserSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $plans = SubscriptionPlan::all();

        foreach ($plans as $plan) {
            $nameLower = strtolower($plan->plan_name);
            $maxUser = 1; // Default

            if (str_contains($nameLower, 'gold') || str_contains($nameLower, 'premium') || str_contains($nameLower, 'mandiri')) {
                $maxUser = 10;
            } elseif (str_contains($nameLower, 'rintis') || str_contains($nameLower, 'bronze') || str_contains($nameLower, 'basic')) {
                $maxUser = 3;
            } else {
                // If it's a higher tier or unknown, give it a default reasonable number
                $maxUser = 5;
            }

            // Update the plan
            $plan->update([
                'max_user' => $maxUser
            ]);
        }
    }
}
