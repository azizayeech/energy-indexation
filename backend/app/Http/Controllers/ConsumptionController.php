<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConsumptionResource;
use App\Models\Consumption;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Dedoc\Scramble\Attributes\Endpoint;
use Dedoc\Scramble\Attributes\Group;

#[Group('Datos de energía')]
final class ConsumptionController extends Controller
{
    #[Endpoint(
        operationId: 'listConsumptions',
        title: 'Consultar consumos',
        description: 'Devuelve los consumos horarios disponibles, ordenados por fecha.'
    )]
    public function __invoke(): AnonymousResourceCollection
    {
        $consumptions = Consumption::query()
            ->orderBy('date')
            ->get();

        return ConsumptionResource::collection($consumptions);
    }
}
