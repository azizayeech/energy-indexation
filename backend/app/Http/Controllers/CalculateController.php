<?php

namespace App\Http\Controllers;

use App\Exceptions\MissingEnergyDataException;
use App\Http\Requests\CalculateRequest;
use App\Services\IndexedPriceCalculator;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Throwable;

class CalculateController extends Controller
{

    public function __construct(
        private readonly IndexedPriceCalculator $calculator
    ) {}

    /**
     * Handle the incoming request.
     */
    public function __invoke(CalculateRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $priceIndexed = $this->calculator->calculate(
                $validated['start_date'],
                $validated['end_date'],
                $validated['formula']
            );

            return response()->json([
                'price_indexed' => $priceIndexed,
            ], 200);
        } catch (MissingEnergyDataException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 404);
        } catch (InvalidArgumentException) {
            return response()->json([
                'message' => 'Se produjo un error al procesar la fórmula.',
            ], 500);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Se produjo un error inesperado durante el cálculo.',
            ], 500);
        }
    }
}
