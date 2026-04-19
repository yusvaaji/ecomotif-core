<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
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
}
