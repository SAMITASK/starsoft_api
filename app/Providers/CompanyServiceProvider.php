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
        $this->app->singleton('companies', function () {
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
