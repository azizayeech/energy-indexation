<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalculateEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_indexed_price_for_valid_request(): void
    {
        DB::table('consumptions')->insert([
            $this->createHourlyRow('2025-03-01', 10),
            $this->createHourlyRow('2025-03-02', 20),
        ]);

        DB::table('prices')->insert([
            $this->createHourlyRow('2025-03-01', 0.10),
            $this->createHourlyRow('2025-03-02', 0.20),
        ]);

        $response = $this->postJson('/calculate', [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-02',
            'formula' => '([OMIE_MD] * 0.6) + 0.88',
        ]);

        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'price_indexed',
            ]);

        $this->assertEqualsWithDelta(
            0.98,
            $response->json('price_indexed'),
            0.000001
        );
    }

    public function test_returns_400_when_start_date_is_missing(): void
    {
        $response = $this->postJson('/calculate', [
            'end_date' => '2025-03-02',
            'formula' => '[OMIE_MD]',
        ]);

        $response
            ->assertStatus(400)
            ->assertJsonValidationErrors([
                'start_date',
            ]);
    }

    public function test_returns_400_when_end_date_is_before_start_date(): void
    {
        $response = $this->postJson('/calculate', [
            'start_date' => '2025-03-02',
            'end_date' => '2025-03-01',
            'formula' => '[OMIE_MD]',
        ]);

        $response
            ->assertStatus(400)
            ->assertJsonValidationErrors([
                'end_date',
            ]);
    }

    public function test_returns_400_when_formula_does_not_contain_omie_md(): void
    {
        $response = $this->postJson('/calculate', [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-02',
            'formula' => '0.10 * 2',
        ]);

        $response
            ->assertStatus(400)
            ->assertJsonValidationErrors([
                'formula',
            ]);
    }

    public function test_returns_404_when_period_data_is_incomplete(): void
    {
        DB::table('consumptions')->insert(
            $this->createHourlyRow('2025-03-01', 10)
        );

        DB::table('prices')->insert(
            $this->createHourlyRow('2025-03-01', 0.10)
        );

        $response = $this->postJson('/calculate', [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-02',
            'formula' => '[OMIE_MD]',
        ]);

        $response
            ->assertStatus(404)
            ->assertJson([
                'message' => 'No existen datos de consumo o precios para todo el período solicitado.',
            ]);
    }

    public function test_rate_limits_calculation_requests(): void
    {
        $this->withServerVariables([
            'REMOTE_ADDR' => '203.0.113.10',
        ]);

        for ($request = 1; $request <= 60; $request++) {
            $this->postJson('/calculate', [])
                ->assertStatus(400);
        }

        $this->postJson('/calculate', [])
            ->assertStatus(429)
            ->assertJson([
                'message' => 'Demasiadas solicitudes. Inténtalo de nuevo en unos instantes.',
            ]);
    }

    public function test_returns_500_for_invalid_formula_expression(): void
    {
        DB::table('consumptions')->insert(
            $this->createHourlyRow('2025-03-01', 10)
        );

        DB::table('prices')->insert(
            $this->createHourlyRow('2025-03-01', 0.10)
        );

        $response = $this->postJson('/calculate', [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-01',
            'formula' => '[OMIE_MD] + phpinfo()',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => 'Se produjo un error al procesar la fórmula.',
            ]);
    }

    private function createHourlyRow(string $date, float $value): array
    {
        $row = [
            'date' => $date,
        ];

        for ($hour = 1; $hour <= 25; $hour++) {
            $row["h{$hour}"] = $value;
        }

        return $row;
    }
}
