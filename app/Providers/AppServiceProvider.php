<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\LineaTransaccion;
use App\Observers\LineaTransaccionObserver;

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
        LineaTransaccion::observe(LineaTransaccionObserver::class);
    }
}
