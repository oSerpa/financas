<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
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
        // /lancamentos/novo e /lancamentos/1/editar em vez de create/edit.
        Route::resourceVerbs(['create' => 'novo', 'edit' => 'editar']);

        Paginator::defaultView('components.pagination');
    }
}
