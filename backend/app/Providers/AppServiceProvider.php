<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Dedoc\Scramble\Scramble;
use Illuminate\Routing\Route;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('calculate', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Demasiadas solicitudes. Inténtalo de nuevo en unos instantes.',
                    ], 429, $headers);
                });
        });

        Scramble::configure()
            ->routes(function (Route $route): bool {
                return in_array($route->uri, [
                    'calculate',
                    'consumptions',
                    'prices',
                ], true);
            });
    }
}
