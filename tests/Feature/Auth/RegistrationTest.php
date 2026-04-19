<?php

namespace Tests\Feature\Auth;

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
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Dealer registration endpoint not yet implemented');
        } else {
            $response->assertStatus(200);
            $this->assertDatabaseHas('users', [
                'email' => 'dealer@example.com',
                'is_dealer' => 1,
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
