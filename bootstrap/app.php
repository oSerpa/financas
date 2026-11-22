<?php

/*
| Ponto de partida da aplicação. Aqui o Laravel 11+ junta o que antes ficava
| espalhado em Kernel.php e RouteServiceProvider: quais arquivos de rota
| carregar, ajustes de middleware e como tratar exceções.
*/

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',         // rotas das páginas
        commands: __DIR__.'/../routes/console.php', // comandos do artisan
        health: '/up',                              // rota de saúde, usada para checar se o site está no ar
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Para onde o middleware 'guest' manda quem já está logado.
        $middleware->redirectUsersTo('/painel');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Erros só viram JSON em rotas de API ou quando o cliente pede JSON.
        // No navegador, a pessoa vê a página de erro normal.
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
