<?php

namespace App\Http\Controllers;

use App\Http\Resources\PriceResource;
use App\Models\Price;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;

#[Group('Datos de energía')]
final class PriceController extends Controller
{
    #[Endpoint(
        operationId: 'listPrices',
        title: 'Consultar precios',
        description: 'Devuelve los precios horarios OMIE disponibles, ordenados por fecha.'
    )]
    public function __invoke(): AnonymousResourceCollection
    {
        $prices = Price::query()
            ->orderBy('date')
            ->get();

        return PriceResource::collection($prices);
    }
}
