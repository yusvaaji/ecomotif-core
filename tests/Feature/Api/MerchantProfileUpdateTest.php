<?php

namespace Tests\Feature\Api;

use App\Models\MerchantProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MerchantProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_merchant_receives_403(): void
    {
        $user = User::factory()->create([
            'is_dealer' => 0,
            'is_garage' => 0,
        ]);

        $this->actingWithJwt($user)
            ->postJson('/api/user/update-merchant-profile', ['pic_name' => 'PIC'])
            ->assertStatus(403);
    }

    public function test_dealer_without_profile_creates_merchant_profile(): void
    {
        $planId = $this->createActiveSubscriptionPlanId();

        $user = User::factory()->create([
            'is_dealer' => 1,
            'is_garage' => 0,
            'status' => 'enable',
        ]);

        $this->assertDatabaseMissing('merchant_profiles', ['user_id' => $user->id]);

        $this->actingWithJwt($user)
            ->postJson('/api/user/update-merchant-profile', [
                'showroom_category' => 'Mobil',
                'showroom_type' => 'Resmi',
                'pic_name' => 'Budi',
                'subscription_plan_id' => $planId,
            ])
            ->assertOk()
            ->assertJsonPath('user.merchant_profile.pic_name', 'Budi');

        $this->assertDatabaseHas('merchant_profiles', [
            'user_id' => $user->id,
            'business_type' => 'showroom',
            'business_category' => 'Mobil',
            'showroom_type' => 'Resmi',
            'subscription_plan_id' => $planId,
        ]);
    }

    public function test_garage_can_update_services_and_category(): void
    {
        $planId = $this->createActiveSubscriptionPlanId();

        $user = User::factory()->create([
            'is_dealer' => 0,
            'is_garage' => 1,
            'status' => 'enable',
        ]);

        MerchantProfile::create([
            'user_id' => $user->id,
            'business_type' => MerchantProfile::BUSINESS_GARAGE,
            'subscription_plan_id' => $planId,
        ]);

        $this->actingWithJwt($user)
            ->postJson('/api/user/update-merchant-profile', [
                'garage_category' => 'Umum',
                'garage_services' => 'Service ringan, tune-up',
            ])
            ->assertOk();

        $user->refresh();
        $this->assertSame('Umum', $user->merchantProfile->business_category);
        $this->assertStringContainsString('Service ringan', (string) $user->merchantProfile->garage_services_description);
    }
}
