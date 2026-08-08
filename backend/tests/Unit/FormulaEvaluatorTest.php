<?php

namespace Tests\Unit;

use App\Services\FormulaEvaluator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class FormulaEvaluatorTest extends TestCase
{
    private FormulaEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->evaluator = new FormulaEvaluator();
    }

    public function test_evaluates_valid_formula(): void
    {
        $result = $this->evaluator->evaluate(
            '([OMIE_MD] * 0.6) + 0.88',
            0.10
        );

        $this->assertEqualsWithDelta(0.94, $result, 0.000001);
    }

    public function test_respects_operator_precedence(): void
    {
        $result = $this->evaluator->evaluate(
            '[OMIE_MD] + 2 * 3',
            1.0
        );

        $this->assertEqualsWithDelta(7.0, $result, 0.000001);
    }

    public function test_respects_parentheses(): void
    {
        $result = $this->evaluator->evaluate(
            '([OMIE_MD] + 2) * 3',
            1.0
        );

        $this->assertEqualsWithDelta(9.0, $result, 0.000001);
    }

    public function test_supports_negative_values(): void
    {
        $result = $this->evaluator->evaluate(
            '-[OMIE_MD] + 2',
            0.5
        );

        $this->assertEqualsWithDelta(1.5, $result, 0.000001);
    }

    public function test_rejects_formula_without_omie_md(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->evaluator->evaluate(
            '1 + 2',
            0.10
        );
    }

    public function test_rejects_unknown_variable(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->evaluator->evaluate(
            '[OMIE_MD] + [OTHER_PRICE]',
            0.10
        );
    }

    public function test_rejects_php_function_calls(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->evaluator->evaluate(
            '[OMIE_MD] + phpinfo()',
            0.10
        );
    }

    public function test_rejects_code_injection_attempt(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->evaluator->evaluate(
            '[OMIE_MD]; system("whoami")',
            0.10
        );
    }

    public function test_rejects_division_by_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->evaluator->evaluate(
            '[OMIE_MD] / 0',
            0.10
        );
    }

    public function test_rejects_invalid_parentheses(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->evaluator->evaluate(
            '([OMIE_MD] + 1',
            0.10
        );
    }

    public function test_rejects_invalid_number(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->evaluator->evaluate(
            '[OMIE_MD] + 1.2.3',
            0.10
        );
    }
}