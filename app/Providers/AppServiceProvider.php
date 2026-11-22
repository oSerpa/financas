<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * Service providers rodam quando a aplicação sobe. É o lugar das
 * configurações globais que valem para o sistema inteiro.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * register(): para registrar serviços no container. Não usamos.
     */
    public function register(): void
    {
        //
    }

    /**
     * boot(): roda depois que tudo foi registrado.
     */
    public function boot(): void
    {
        // Traduz as palavras que o Route::resource coloca na URL:
        // /lancamentos/novo e /lancamentos/1/editar em vez de create/edit.
        Route::resourceVerbs(['create' => 'novo', 'edit' => 'editar']);

        // Troca o HTML padrão da paginação ({{ $lista->links() }}) pelo
        // nosso componente em resources/views/components/pagination.blade.php.
        Paginator::defaultView('components.pagination');
    }
}
