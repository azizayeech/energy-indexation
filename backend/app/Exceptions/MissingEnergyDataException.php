<?php

namespace App\Exceptions;

use RuntimeException;

final class MissingEnergyDataException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'No existen datos de consumo o precios para todo el período solicitado.'
        );
    }
}