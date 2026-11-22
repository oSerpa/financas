<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/painel');

Route::middleware('guest')->group(function () {
    Route::get('/entrar', [LoginController::class, 'create'])->name('login');
    Route::post('/entrar', [LoginController::class, 'store']);

    Route::get('/cadastro', [RegisterController::class, 'create'])->name('register');
    Route::post('/cadastro', [RegisterController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/painel', DashboardController::class)->name('dashboard');
    Route::post('/sair', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/lancamentos/exportar', [TransactionController::class, 'export'])->name('transactions.export');

    // URLs em português, nomes de rota em inglês para manter o código consistente.
    Route::resource('lancamentos', TransactionController::class)
        ->except('show')
        ->parameters(['lancamentos' => 'transaction'])
        ->names('transactions');

    Route::resource('categorias', CategoryController::class)
        ->except('show')
        ->parameters(['categorias' => 'category'])
        ->names('categories');
});
