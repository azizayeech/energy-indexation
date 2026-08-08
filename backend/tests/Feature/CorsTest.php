<?php

namespace Tests\Feature;

use Tests\TestCase;

class CorsTest extends TestCase
{
    public function test_allows_configured_frontend_origin(): void
    {
        config([
            'cors.allowed_origins' => ['http://localhost:5173'],
        ]);

        $response = $this
            ->withHeaders([
                'Origin' => 'http://localhost:5173',
                'Access-Control-Request-Method' => 'POST',
                'Access-Control-Request-Headers' => 'Content-Type',
            ])
            ->options('/calculate');

        $response
            ->assertSuccessful()
            ->assertHeader(
                'Access-Control-Allow-Origin',
                'http://localhost:5173'
            );
    }

    public function test_does_not_allow_unknown_origin(): void
    {
        config([
            'cors.allowed_origins' => ['http://localhost:5173'],
        ]);

        $response = $this
            ->withHeaders([
                'Origin' => 'https://malicious.example',
                'Access-Control-Request-Method' => 'POST',
                'Access-Control-Request-Headers' => 'Content-Type',
            ])
            ->options('/calculate');

        $response
            ->assertSuccessful()
            ->assertHeader(
                'Access-Control-Allow-Origin',
                'http://localhost:5173'
            );

        $this->assertNotSame(
            'https://malicious.example',
            $response->headers->get('Access-Control-Allow-Origin')
        );
    }
}
