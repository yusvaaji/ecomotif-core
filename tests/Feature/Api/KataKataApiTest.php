<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class KataKataApiTest extends TestCase
{
    public function test_kata_kata_index_returns_quotes_array(): void
    {
        $response = $this->getJson('/api/kata-kata');

        $response->assertStatus(200)
            ->assertJsonStructure(['quotes']);

        $this->assertNotEmpty($response->json('quotes'));
    }

    public function test_kata_kata_random_returns_single_quote(): void
    {
        $response = $this->getJson('/api/kata-kata/random');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'quote' => ['id', 'text'],
            ]);
    }

    public function test_kata_kata_index_respects_locale_filter(): void
    {
        $response = $this->getJson('/api/kata-kata?locale=en');

        $response->assertStatus(200);
        foreach ($response->json('quotes') as $q) {
            $this->assertContains($q['locale'] ?? null, ['en', 'mixed']);
        }
    }
}
