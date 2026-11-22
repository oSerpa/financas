<?php

/*
|--------------------------------------------------------------------------
| Rotas web da Caderneta
|--------------------------------------------------------------------------
|
| Cada rota liga uma URL + método HTTP a um método de controller.
| O ->name() dá um apelido à rota, usado com route('nome') nas views e
| nos redirects. Assim, se a URL mudar, nada mais precisa mudar.
|
| Para ver todas as rotas no terminal: php artisan route:list
|
*/

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

// A raiz do site só manda para o painel. Se a pessoa não estiver logada,
// o middleware 'auth' do painel a manda para a tela de login.
Route::redirect('/', '/painel');

// Rotas para visitantes (middleware 'guest'): quem já está logado e tenta
// abrir estas páginas é redirecionado para o painel (ver bootstrap/app.php).
Route::middleware('guest')->group(function () {
    // GET mostra o formulário, POST recebe o envio do formulário.
    Route::get('/entrar', [LoginController::class, 'create'])->name('login');
    Route::post('/entrar', [LoginController::class, 'store']);

    Route::get('/cadastro', [RegisterController::class, 'create'])->name('register');
    Route::post('/cadastro', [RegisterController::class, 'store']);

    // Esqueci minha senha. O throttle:6,1 limita a 6 envios por minuto
    // para ninguém usar o formulário para disparar e-mails em massa.
    Route::get('/esqueci-senha', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('/esqueci-senha', [ForgotPasswordController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('password.email');

    // O {token} vem no link do e-mail e prova que a pessoa recebeu a mensagem.
    // O nome 'password.reset' é o que o Laravel usa para montar esse link.
    Route::get('/redefinir-senha/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('/redefinir-senha', [ResetPasswordController::class, 'store'])->name('password.update');
});

// Rotas protegidas (middleware 'auth'): só abrem com usuário logado.
Route::middleware('auth')->group(function () {
    // DashboardController tem um único método __invoke, por isso não
    // precisa do array [Classe, 'metodo'].
    Route::get('/painel', DashboardController::class)->name('dashboard');

    // Sair é POST (e não GET) para exigir o token CSRF do formulário.
    Route::post('/sair', [LoginController::class, 'destroy'])->name('logout');

    // Fica ANTES do resource: senão "exportar" seria lido como um {transaction}.
    Route::get('/lancamentos/exportar', [TransactionController::class, 'export'])->name('transactions.export');

    // Route::resource cria de uma vez as rotas do CRUD:
    //   GET    /lancamentos                     index   (lista)
    //   GET    /lancamentos/novo                create  (formulário de novo)
    //   POST   /lancamentos                     store   (salva o novo)
    //   GET    /lancamentos/{transaction}/editar edit   (formulário de edição)
    //   PUT    /lancamentos/{transaction}       update  (salva a edição)
    //   DELETE /lancamentos/{transaction}       destroy (exclui)
    // As palavras "novo" e "editar" vêm do AppServiceProvider.
    // URLs em português, nomes de rota em inglês para manter o código consistente.
    Route::resource('lancamentos', TransactionController::class)
        ->except('show') // não existe página de detalhe de um lançamento
        ->parameters(['lancamentos' => 'transaction']) // {lancamento} vira {transaction}, igual ao nome do parâmetro no controller
        ->names('transactions'); // rotas viram transactions.index, transactions.store...

    // Mesmo esquema para categorias: categories.index, categories.create...
    Route::resource('categorias', CategoryController::class)
        ->except('show')
        ->parameters(['categorias' => 'category'])
        ->names('categories');
});
