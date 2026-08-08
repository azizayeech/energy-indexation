<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalculateSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_formula_longer_than_500_characters(): void
    {
        $formula = '[OMIE_MD] + ' . str_repeat('1', 500);

        $response = $this->postJson('/calculate', [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-01',
            'formula' => $formula,
        ]);

        $response
            ->assertStatus(400)
            ->assertJsonValidationErrors('formula');
    }

    public function test_does_not_execute_php_functions(): void
    {
        $this->insertValidEnergyData();

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

    public function test_rejects_command_injection_attempt(): void
    {
        $this->insertValidEnergyData();

        $response = $this->postJson('/calculate', [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-01',
            'formula' => '[OMIE_MD]; system("whoami")',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => 'Se produjo un error al procesar la fórmula.',
            ]);
    }

    public function test_rejects_unknown_variables(): void
    {
        $this->insertValidEnergyData();

        $response = $this->postJson('/calculate', [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-01',
            'formula' => '[OMIE_MD] + [OTHER_VARIABLE]',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => 'Se produjo un error al procesar la fórmula.',
            ]);
    }

    public function test_rejects_invalid_parentheses(): void
    {
        $this->insertValidEnergyData();

        $response = $this->postJson('/calculate', [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-01',
            'formula' => '([OMIE_MD] * 2',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => 'Se produjo un error al procesar la fórmula.',
            ]);
    }

    public function test_rejects_division_by_zero(): void
    {
        $this->insertValidEnergyData();

        $response = $this->postJson('/calculate', [
            'start_date' => '2025-03-01',
            'end_date' => '2025-03-01',
            'formula' => '[OMIE_MD] / 0',
        ]);

        $response
            ->assertStatus(500)
            ->assertJson([
                'message' => 'Se produjo un error al procesar la fórmula.',
            ]);
    }

    private function insertValidEnergyData(): void
    {
        DB::table('consumptions')->insert(
            $this->createHourlyRow('2025-03-01', 10)
        );

        DB::table('prices')->insert(
            $this->createHourlyRow('2025-03-01', 0.10)
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