<?php

namespace App\Services;

use InvalidArgumentException;

final class FormulaEvaluator
{
    private const OMIE_VARIABLE = '[OMIE_MD]';

    private array $tokens = [];

    private int $position = 0;

    public function evaluate(string $formula, float $omiePrice): float
    {
        if (! str_contains($formula, self::OMIE_VARIABLE)) {
            throw new InvalidArgumentException(
                'La fórmula debe contener el segmento [OMIE_MD].'
            );
        }

        $this->tokens = $this->tokenize($formula, $omiePrice);
        $this->position = 0;

        if ($this->tokens === []) {
            throw new InvalidArgumentException('La fórmula está vacía.');
        }

        $result = $this->parseExpression();

        if ($this->position !== count($this->tokens)) {
            throw new InvalidArgumentException(
                'La fórmula contiene una expresión no válida.'
            );
        }

        if (! is_finite($result)) {
            throw new InvalidArgumentException(
                'El resultado de la fórmula no es válido.'
            );
        }

        return $result;
    }

    private function tokenize(string $formula, float $omiePrice): array
    {
        $tokens = [];
        $length = strlen($formula);
        $index = 0;

        while ($index < $length) {
            $character = $formula[$index];

            // Ignore spaces
            if (ctype_space($character)) {
                $index++;
                continue;
            }

            // Allowed variable: [OMIE_MD]
            if (
                substr(
                    $formula,
                    $index,
                    strlen(self::OMIE_VARIABLE)
                ) === self::OMIE_VARIABLE
            ) {
                $tokens[] = [
                    'type' => 'number',
                    'value' => $omiePrice,
                ];

                $index += strlen(self::OMIE_VARIABLE);

                continue;
            }

            // Allowed operators and parentheses
            if (str_contains('+-*/()', $character)) {
                $tokens[] = [
                    'type' => $character,
                    'value' => $character,
                ];

                $index++;

                continue;
            }

            // Numbers
            if (ctype_digit($character) || $character === '.') {
                $start = $index;
                $decimalPoints = 0;

                while (
                    $index < $length
                    && (
                        ctype_digit($formula[$index])
                        || $formula[$index] === '.'
                    )
                ) {
                    if ($formula[$index] === '.') {
                        $decimalPoints++;
                    }

                    if ($decimalPoints > 1) {
                        throw new InvalidArgumentException(
                            'La fórmula contiene un número no válido.'
                        );
                    }

                    $index++;
                }

                $number = substr($formula, $start, $index - $start);

                if ($number === '.' || ! is_numeric($number)) {
                    throw new InvalidArgumentException(
                        'La fórmula contiene un número no válido.'
                    );
                }

                $tokens[] = [
                    'type' => 'number',
                    'value' => (float) $number,
                ];

                continue;
            }

            // Anything else is rejected
            throw new InvalidArgumentException(
                'La fórmula contiene caracteres o elementos no permitidos.'
            );
        }

        return $tokens;
    }

    private function parseExpression(): float
    {
        $value = $this->parseTerm();

        while (
            $this->currentType() === '+'
            || $this->currentType() === '-'
        ) {
            $operator = $this->currentType();

            $this->position++;

            $right = $this->parseTerm();

            $value = $operator === '+'
                ? $value + $right
                : $value - $right;
        }

        return $value;
    }

    private function parseTerm(): float
    {
        $value = $this->parseFactor();

        while (
            $this->currentType() === '*'
            || $this->currentType() === '/'
        ) {
            $operator = $this->currentType();

            $this->position++;

            $right = $this->parseFactor();

            if ($operator === '/') {
                if ($right == 0.0) {
                    throw new InvalidArgumentException(
                        'No se permite la división por cero.'
                    );
                }

                $value /= $right;

                continue;
            }

            $value *= $right;
        }

        return $value;
    }

    private function parseFactor(): float
    {
        $type = $this->currentType();

        if ($type === null) {
            throw new InvalidArgumentException(
                'La fórmula está incompleta.'
            );
        }

        // Unary +
        if ($type === '+') {
            $this->position++;

            return $this->parseFactor();
        }

        // Unary -
        if ($type === '-') {
            $this->position++;

            return -$this->parseFactor();
        }

        if ($type === 'number') {
            $value = $this->tokens[$this->position]['value'];

            $this->position++;

            return $value;
        }

        if ($type === '(') {
            $this->position++;

            $value = $this->parseExpression();

            if ($this->currentType() !== ')') {
                throw new InvalidArgumentException(
                    'Los paréntesis de la fórmula no son válidos.'
                );
            }

            $this->position++;

            return $value;
        }

        throw new InvalidArgumentException(
            'La fórmula contiene una expresión no válida.'
        );
    }

    private function currentType(): ?string
    {
        return $this->tokens[$this->position]['type'] ?? null;
    }
}