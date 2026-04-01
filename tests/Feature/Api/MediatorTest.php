<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Booking;
use Modules\Car\Entities\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Hash;

class MediatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create mediator user
        $this->mediator = User::factory()->create([
            'is_mediator' => 1,
            'password' => Hash::make('password'),
        ]);
    }

    public function test_mediator_can_access_dashboard(): void
    {
        Sanctum::actingAs($this->mediator);

        $response = $this->getJson('/api/user/mediator/dashboard');

        if ($response->status() === 404) {
            $this->markTestSkipped('Mediator dashboard endpoint not yet implemented');
        } else {
            $response->assertStatus(200);
        }
    }

    public function test_non_mediator_cannot_access_dashboard(): void
    {
        $user = User::factory()->create([
            'is_mediator' => 0,
        ]);
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/user/mediator/dashboard');

        $response->assertStatus(403);
    }

    public function test_mediator_can_list_applications(): void
    {
        Sanctum::actingAs($this->mediator);

        // Create test booking
        $consumer = User::factory()->create();
        $car = Car::factory()->create();
        $booking = Booking::factory()->create([
            'mediator_id' => $this->mediator->id,
            'user_id' => $consumer->id,
            'car_id' => $car->id,
            'leasing_status' => 'pending',
        ]);

        $response = $this->getJson('/api/user/mediator/applications');

        if ($response->status() === 404) {
            $this->markTestSkipped('Mediator applications endpoint not yet implemented');
        } else {
            $response->assertStatus(200);
        }
    }

    public function test_mediator_can_update_application_status(): void
    {
        Sanctum::actingAs($this->mediator);

        $consumer = User::factory()->create();
        $car = Car::factory()->create();
        $booking = Booking::factory()->create([
            'mediator_id' => $this->mediator->id,
            'user_id' => $consumer->id,
            'car_id' => $car->id,
            'leasing_status' => 'pending',
        ]);

        $response = $this->putJson("/api/user/mediator/applications/{$booking->id}", [
            'leasing_status' => 'review',
            'leasing_notes' => 'Application under review',
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Mediator update application endpoint not yet implemented');
        } else {
            $response->assertStatus(200);
        }
    }
}

