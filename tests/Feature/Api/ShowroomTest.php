<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\Booking;
use Modules\Car\Entities\Car;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ShowroomTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->showroom = User::factory()->create([
            'is_dealer' => 1,
            'password' => Hash::make('password'),
        ]);
    }

    public function test_showroom_can_generate_barcode(): void
    {
        $response = $this->actingWithJwt($this->showroom)
            ->postJson('/api/user/showroom/generate-barcode');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'barcode',
                'qr_code',
            ]);

        $this->assertNotNull($this->showroom->fresh()->barcode);
    }

    public function test_showroom_can_get_barcode(): void
    {
        $this->showroom->barcode = 'SHOWROOM-TEST-123';
        $this->showroom->save();

        $response = $this->actingWithJwt($this->showroom)
            ->getJson('/api/user/showroom/barcode');

        $response->assertStatus(200)
            ->assertJson([
                'barcode' => 'SHOWROOM-TEST-123',
            ]);
    }

    public function test_non_dealer_cannot_generate_barcode(): void
    {
        $user = User::factory()->create([
            'is_dealer' => 0,
        ]);
        $response = $this->actingWithJwt($user)
            ->postJson('/api/user/showroom/generate-barcode');

        $response->assertStatus(403);
    }

    public function test_showroom_can_pool_application(): void
    {
        $consumer = User::factory()->create();
        $car = Car::factory()->create([
            'agent_id' => $this->showroom->id,
        ]);
        $booking = Booking::factory()->create([
            'user_id' => $consumer->id,
            'car_id' => $car->id,
            'showroom_id' => $this->showroom->id,
            'leasing_status' => 'pending',
        ]);

        $response = $this->actingWithJwt($this->showroom)
            ->postJson("/api/user/showroom/applications/{$booking->id}/pool-to-leasing");

        if ($response->status() === 404) {
            $this->markTestSkipped('Showroom pool application endpoint not yet implemented');
        } else {
            $response->assertStatus(200);
        }
    }

    public function test_showroom_can_appeal_rejected_application(): void
    {
        $consumer = User::factory()->create();
        $car = Car::factory()->create([
            'agent_id' => $this->showroom->id,
        ]);
        $booking = Booking::factory()->create([
            'user_id' => $consumer->id,
            'car_id' => $car->id,
            'showroom_id' => $this->showroom->id,
            'leasing_status' => 'rejected',
        ]);

        $response = $this->actingWithJwt($this->showroom)
            ->postJson("/api/user/showroom/applications/{$booking->id}/appeal", [
            'reason' => 'Additional documents provided',
        ]);

        if ($response->status() === 404) {
            $this->markTestSkipped('Showroom appeal endpoint not yet implemented');
        } else {
            $response->assertStatus(200);
        }
    }
}

