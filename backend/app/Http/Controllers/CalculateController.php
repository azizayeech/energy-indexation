<?php

namespace App\Http\Controllers;

use App\Exceptions\MissingEnergyDataException;
use App\Http\Requests\CalculateRequest;
use App\Services\IndexedPriceCalculator;
use Illuminate\Http\JsonResponse;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\IgnoreResponse;
use Dedoc\Scramble\Attributes\Response;
use Dedoc\Scramble\Attributes\BodyParameter;
use Dedoc\Scramble\Attributes\Group;
use InvalidArgumentException;
use Throwable;

#[Group('Cálculo')]
class CalculateController extends Controller
{

    public function __construct(
        private readonly IndexedPriceCalculator $calculator
    ) {}

    /**
     * Handle the incoming request.
     */

    #[Endpoint(
        operationId: 'calculateIndexedPrice',
        title: 'Calcular precio indexado',
        description: 'Calcula el precio indexado de la energía para un período determinado aplicando una fórmula sobre el precio horario OMIE_MD.'
    )]
    #[BodyParameter(
        'start_date',
        description: 'Fecha inicial del período en formato YYYY-MM-DD.',
        example: '2025-03-01'
    )]
    #[BodyParameter(
        'end_date',
        description: 'Fecha final del período en formato YYYY-MM-DD.',
        example: '2025-03-02'
    )]
    #[BodyParameter(
        'formula',
        description: 'Fórmula matemática que debe contener la variable [OMIE_MD].',
        example: '([OMIE_MD] * 0.6) + 0.88'
    )]
    #[IgnoreResponse(422)]
    #[Response(
        200,
        'Cálculo realizado correctamente. El campo price_indexed se expresa en €/kWh.',
        type: 'array{price_indexed: float}',
        examples: [
            ['price_indexed' => 0.98],
        ]
    )]
    #[Response(
        400,
        'Datos de entrada inválidos o incompletos.',
        type: 'array{message: string, errors: array}'
    )]
    #[Response(
        404,
        'No existen datos de consumo o precios para todo el período solicitado.',
        type: 'array{message: string}'
    )]
    #[Response(
        429,
        'Se ha superado el límite de solicitudes.',
        type: 'array{message: string}'
    )]
    #[Response(
        500,
        'Error durante el procesamiento del cálculo o de la fórmula.',
        type: 'array{message: string}'
    )]
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
