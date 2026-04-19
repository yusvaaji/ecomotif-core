<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_quotes_leasing_returns_installment_payload(): void
    {
        $response = $this->postJson('/api/quotes/leasing', [
            'car_price' => 200000000,
            'down_payment' => 40000000,
            'tenure_months' => 36,
            'interest_rate' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'car_price',
                'down_payment',
                'loan_amount',
                'monthly_installment',
                'tenure_months',
                'interest_rate',
                'total_payment',
            ]);
    }

    public function test_quotes_leasing_rejects_down_payment_gte_price(): void
    {
        $response = $this->postJson('/api/quotes/leasing', [
            'car_price' => 100,
            'down_payment' => 100,
        ]);

        $response->assertStatus(403);
    }

    public function test_seller_register_requires_terms_accepted(): void
    {
        $response = $this->postJson('/api/seller/store-register', [
            'name' => 'Showroom Test',
            'email' => 'showroom-test-'.uniqid().'@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Test',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['terms_accepted']);
    }

    public function test_guest_service_booking_requires_fields(): void
    {
        $response = $this->postJson('/api/guest/service-bookings', []);

        $response->assertStatus(422);
    }
}
