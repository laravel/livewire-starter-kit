<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        // Registrar observer para Price
        \App\Models\Price::observe(\App\Observers\PriceObserver::class);

        // Registrar observer de Empaque -> Shipping para Lot.
        // Detecta el closure_decision y activa ready_for_shipping en el lote.
        \App\Models\Lot::observe(\App\Observers\LotPackagingObserver::class);
    }
}
