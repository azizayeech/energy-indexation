<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConsumptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
        ];

        for ($hour = 1; $hour <= 25; $hour++) {
            $column = "h{$hour}";
            $data[$column] = (float) $this->{$column};
        }

        return $data;
    }
}