<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EnergyDataEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_consumptions_ordered_by_date(): void
    {
        DB::table('consumptions')->insert([
            $this->createHourlyRow('2025-03-02', 20),
            $this->createHourlyRow('2025-03-01', 10),
        ]);

        $response = $this->getJson('/consumptions');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.date', '2025-03-01')
            ->assertJsonPath('data.1.date', '2025-03-02')
            ->assertJsonPath('data.0.h1', 10)
            ->assertJsonPath('data.0.h25', 10);
    }

    public function test_returns_prices_ordered_by_date(): void
    {
        DB::table('prices')->insert([
            $this->createHourlyRow('2025-03-02', 0.20),
            $this->createHourlyRow('2025-03-01', 0.10),
        ]);

        $response = $this->getJson('/prices');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.date', '2025-03-01')
            ->assertJsonPath('data.1.date', '2025-03-02')
            ->assertJsonPath('data.0.h1', 0.10)
            ->assertJsonPath('data.0.h25', 0.10);
    }

    public function test_consumption_response_contains_all_25_hourly_values(): void
    {
        DB::table('consumptions')->insert(
            $this->createHourlyRow('2025-03-01', 10)
        );

        $response = $this->getJson('/consumptions');

        $expectedKeys = [
            'id',
            'date',
        ];

        for ($hour = 1; $hour <= 25; $hour++) {
            $expectedKeys[] = "h{$hour}";
        }

        $response
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => $expectedKeys,
                ],
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