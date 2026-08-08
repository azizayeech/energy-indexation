<?php

namespace Tests\Feature;

use App\Services\IndexedPriceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use App\Exceptions\MissingEnergyDataException;
use Tests\TestCase;

class IndexedPriceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_weighted_indexed_price(): void
    {
        DB::table('consumptions')->insert([
            $this->createHourlyRow('2025-03-01', 10),
            $this->createHourlyRow('2025-03-02', 20),
        ]);

        DB::table('prices')->insert([
            $this->createHourlyRow('2025-03-01', 0.10),
            $this->createHourlyRow('2025-03-02', 0.20),
        ]);

        $calculator = app(IndexedPriceCalculator::class);

        $result = $calculator->calculate(
            '2025-03-01',
            '2025-03-02',
            '[OMIE_MD]'
        );

        $this->assertEqualsWithDelta(
            0.1666666667,
            $result,
            0.000001
        );
    }

    public function test_applies_formula_before_weighting_by_consumption(): void
    {
        DB::table('consumptions')->insert([
            $this->createHourlyRow('2025-03-01', 10),
            $this->createHourlyRow('2025-03-02', 20),
        ]);

        DB::table('prices')->insert([
            $this->createHourlyRow('2025-03-01', 0.10),
            $this->createHourlyRow('2025-03-02', 0.20),
        ]);

        $calculator = app(IndexedPriceCalculator::class);

        $result = $calculator->calculate(
            '2025-03-01',
            '2025-03-02',
            '([OMIE_MD] * 0.6) + 0.88'
        );

        $this->assertEqualsWithDelta(
            0.98,
            $result,
            0.000001
        );
    }

    public function test_fails_when_data_is_missing_for_part_of_period(): void
    {
        DB::table('consumptions')->insert(
            $this->createHourlyRow('2025-03-01', 10)
        );

        DB::table('prices')->insert(
            $this->createHourlyRow('2025-03-01', 0.10)
        );

        $calculator = app(IndexedPriceCalculator::class);

        $this->expectException(MissingEnergyDataException::class);

        $calculator->calculate(
            '2025-03-01',
            '2025-03-02',
            '[OMIE_MD]'
        );
    }

    public function test_fails_when_total_consumption_is_zero(): void
    {
        DB::table('consumptions')->insert(
            $this->createHourlyRow('2025-03-01', 0)
        );

        DB::table('prices')->insert(
            $this->createHourlyRow('2025-03-01', 0.10)
        );

        $calculator = app(IndexedPriceCalculator::class);

        $this->expectException(RuntimeException::class);

        $calculator->calculate(
            '2025-03-01',
            '2025-03-01',
            '[OMIE_MD]'
        );
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
