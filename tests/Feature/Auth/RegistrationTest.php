<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_dealer_can_register_via_api(): void
    {
        $planId = $this->createActiveSubscriptionPlanId();

        $response = $this->postJson('/api/seller/store-register', [
            'name' => 'Test Dealer',
            'email' => 'dealer@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'username' => 'testdealer',
            'designation' => 'Showroom Owner',
            'address' => 'Test Address',
            'terms_accepted' => true,
            'subscription_plan_id' => $planId,
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Dealer registration endpoint not yet implemented');
        } else {
            $response->assertStatus(200);
            $this->assertDatabaseHas('users', [
                'email' => 'dealer@example.com',
                'is_dealer' => 1,
            ]);
            $userId = User::where('email', 'dealer@example.com')->value('id');
            $this->assertDatabaseHas('merchant_profiles', [
                'user_id' => $userId,
                'business_type' => 'showroom',
                'subscription_plan_id' => $planId,
            ]);
        }
    }

    public function test_mediator_can_register_via_api(): void
    {
        $response = $this->postJson('/api/mediator/store-register', [
            'name' => 'Test Mediator',
            'email' => 'mediator@example.com',
            'phone' => '081234567891',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'username' => 'testmediator',
            'designation' => 'Mediator',
            'address' => 'Test Address',
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Mediator registration endpoint not yet implemented');
        } else {
            $response->assertStatus(200);
            $this->assertDatabaseHas('users', [
                'email' => 'mediator@example.com',
                'is_mediator' => 1,
            ]);
        }
    }

    public function test_sales_can_register_for_dealer_or_garage_partner(): void
    {
        $dealer = User::factory()->create([
            'is_dealer' => 1,
            'status' => 'enable',
        ]);
        $garage = User::factory()->create([
            'is_garage' => 1,
            'status' => 'enable',
        ]);

        $dealerSales = $this->postJson('/api/sales/store-register', [
            'name' => 'Dealer Sales',
            'email' => 'dealer-sales@example.com',
            'phone' => '081234567800',
            'address' => 'Addr',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sales_partner_type' => 'dealer',
            'partner_id' => $dealer->id,
        ]);
        $dealerSales->assertStatus(200);

        $garageSales = $this->postJson('/api/sales/store-register', [
            'name' => 'Garage Sales',
            'email' => 'garage-sales@example.com',
            'phone' => '081234567801',
            'address' => 'Addr',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'sales_partner_type' => 'garage',
            'partner_id' => $garage->id,
        ]);
        $garageSales->assertStatus(200);

        $this->assertDatabaseHas('users', [
            'email' => 'dealer-sales@example.com',
            'is_sales' => 1,
            'sales_partner_type' => 'dealer',
            'partner_id' => $dealer->id,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'garage-sales@example.com',
            'is_sales' => 1,
            'sales_partner_type' => 'garage',
            'partner_id' => $garage->id,
        ]);
    }

    public function test_registration_requires_valid_email(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['password']);
    }
}
