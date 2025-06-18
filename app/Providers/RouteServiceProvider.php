<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Регистрация маршрутов.
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            // API маршруты
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Маршруты для админ-панели
            Route::middleware('admin')
                ->prefix('admin')
                ->group(base_path('routes/admin.php'));
            
            // Маршруты для AJAX-запросов
            Route::middleware('ajax')
                ->prefix('ajax')
                ->group(base_path('routes/ajax.php'));
        });
    }

    /**
     * Настройки для ограничения числа обращений за период времени
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}
