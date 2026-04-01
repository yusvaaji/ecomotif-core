<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculator_returns_valid_payment_capability(): void
    {
        $response = $this->postJson('/api/calculator/payment-capability', [
            'monthly_income' => 10000000,
            'monthly_expenses' => 5000000,
            'existing_loans' => 2000000,
            'car_price' => 200000000,
            'tenure_months' => 36,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
            ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('data', $data);
    }

    public function test_calculator_requires_monthly_income(): void
    {
        $response = $this->postJson('/api/calculator/payment-capability', [
            'monthly_expenses' => 5000000,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['monthly_income']);
    }

    public function test_calculator_handles_zero_expenses(): void
    {
        $response = $this->postJson('/api/calculator/payment-capability', [
            'monthly_income' => 10000000,
            'monthly_expenses' => 0,
            'existing_loans' => 0,
            'car_price' => 200000000,
            'tenure_months' => 36,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
    }

    public function test_calculator_handles_high_expenses(): void
    {
        $response = $this->postJson('/api/calculator/payment-capability', [
            'monthly_income' => 10000000,
            'monthly_expenses' => 12000000, // More than income
            'existing_loans' => 0,
            'car_price' => 200000000,
            'tenure_months' => 36,
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertTrue($data['success']);
    }
}

