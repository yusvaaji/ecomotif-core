<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_dealer_can_authenticate(): void
    {
        $dealer = User::factory()->create([
            'is_dealer' => 1,
            'password' => bcrypt('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $dealer->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_mediator_can_authenticate(): void
    {
        $mediator = User::factory()->create([
            'is_mediator' => 1,
            'password' => Hash::make('password'),
        ]);

        $response = $this->post('/login', [
            'email' => $mediator->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_api_login_returns_user_type_for_mediator(): void
    {
        $mediator = User::factory()->create([
            'is_mediator' => 1,
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $mediator->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token',
                'user_type'
            ])
            ->assertJson([
                'user_type' => 'mediator'
            ]);
    }

    public function test_api_login_returns_user_type_for_dealer(): void
    {
        $dealer = User::factory()->create([
            'is_dealer' => 1,
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $dealer->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user',
                'token',
                'user_type'
            ])
            ->assertJson([
                'user_type' => 'dealer'
            ]);
    }
}
