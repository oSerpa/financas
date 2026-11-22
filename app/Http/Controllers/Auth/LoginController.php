<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Entrar e sair do sistema.
 */
class LoginController extends Controller
{
    /** GET /entrar: mostra o formulário de login. */
    public function create(): View
    {
        return view('auth.login');
    }

    /** POST /entrar: confere e-mail e senha e abre a sessão. */
    public function store(Request $request): RedirectResponse
    {
        // validate() devolve os dados válidos ou volta ao formulário com os erros.
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Limita tentativas por e-mail + IP para dificultar força bruta.
        $throttleKey = Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($throttleKey)]),
            ]);
        }

        // Auth::attempt busca o usuário pelo e-mail e compara a senha com o hash.
        // O segundo argumento é o "lembrar de mim" (cookie de longa duração).
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($throttleKey);
        // Gera um novo id de sessão após o login, contra session fixation.
        $request->session()->regenerate();

        // intended() leva para a página que a pessoa tentou abrir antes de
        // ser mandada ao login. Se não houver, vai para o painel.
        return redirect()->intended(route('dashboard'));
    }

    /** POST /sair: encerra a sessão. */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        // Apaga os dados da sessão e troca o token CSRF.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
