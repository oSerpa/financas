<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Cadastro de nova conta.
 */
class RegisterController extends Controller
{
    /** GET /cadastro: formulário de cadastro. */
    public function create(): View
    {
        $this->ensureRegistrationIsOpen();

        return view('auth.register');
    }

    /** POST /cadastro: cria a conta, as categorias padrão e já faz login. */
    public function store(Request $request): RedirectResponse
    {
        $this->ensureRegistrationIsOpen();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // unique:users recusa e-mail que já tem conta.
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            // confirmed exige um campo password_confirmation igual.
            // Password::defaults() é a regra padrão de senha (mínimo de 8 caracteres).
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // A senha é convertida em hash pelo cast 'hashed' do model User.
        $user = User::create($data);
        $user->createDefaultCategories();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * Com REGISTRATION_ENABLED=false a tela e o envio do cadastro respondem 404.
     * O valor é lido em config/app.php, que lê o .env.
     */
    private function ensureRegistrationIsOpen(): void
    {
        abort_unless(config('app.registration_enabled'), 404);
    }
}
