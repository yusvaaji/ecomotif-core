<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * API routes use guard `api` (JWT), not Sanctum.
     */
    protected function actingWithJwt(User $user): self
    {
        $token = JWTAuth::fromUser($user);

        return $this->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
        ]);
    }

    /**
     * Active row in subscription_plans (required for mitra register tests).
     */
    protected function createActiveSubscriptionPlanId(): int
    {
        return (int) DB::table('subscription_plans')->insertGetId([
            'plan_name' => 'Test Plan',
            'plan_price' => 0,
            'expiration_date' => '30',
            'max_car' => 10,
            'featured_car' => 2,
            'status' => 'active',
            'serial' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
