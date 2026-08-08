<?php

namespace App\Services;

use App\Models\Consumption;
use App\Models\Price;
use Carbon\CarbonPeriod;
use RuntimeException;
use App\Exceptions\MissingEnergyDataException;

final class IndexedPriceCalculator
{
    public function __construct(
        private readonly FormulaEvaluator $formulaEvaluator
    ) {}

    public function calculate(
        string $startDate,
        string $endDate,
        string $formula
    ): float {
        $period = CarbonPeriod::create($startDate, $endDate);

        $consumptions = Consumption::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(
                fn(Consumption $consumption) =>
                $consumption->date->format('Y-m-d')
            );

        $prices = Price::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->keyBy(
                fn(Price $price) =>
                $price->date->format('Y-m-d')
            );

        $totalAmount = 0.0;
        $totalConsumption = 0.0;

        foreach ($period as $date) {
            $formattedDate = $date->format('Y-m-d');

            $consumption = $consumptions->get($formattedDate);
            $price = $prices->get($formattedDate);

            if ($consumption === null || $price === null) {
                throw new MissingEnergyDataException();
            }

            for ($hour = 1; $hour <= 25; $hour++) {
                $column = "h{$hour}";

                $hourConsumption = (float) $consumption->{$column};
                $omiePrice = (float) $price->{$column};

                $evaluatedPrice = $this->formulaEvaluator->evaluate(
                    $formula,
                    $omiePrice
                );

                $totalAmount += $evaluatedPrice * $hourConsumption;
                $totalConsumption += $hourConsumption;
            }
        }

        if ($totalConsumption <= 0) {
            throw new RuntimeException(
                'El consumo total del período debe ser mayor que cero.'
            );
        }

        return $totalAmount / $totalConsumption;
    }
}
