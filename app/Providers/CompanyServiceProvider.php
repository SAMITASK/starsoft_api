<?php

namespace App\Providers;

use App\Models\CompanyModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class CompanyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registramos un singleton para que toda la app use el mismo objeto
        $this->app->singleton('companies', function () {
            // Guardamos en cache por 1 día (puedes ajustar el tiempo)
            return Cache::remember('companies', 60 * 60 * 24, function () {
                return CompanyModel::on('bdwenco')->get(['EMP_CODIGO', 'EMP_RAZON_NOMBRE']);
            });
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
