<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Segundo passo do "esqueci minha senha": a pessoa chega pelo link do
 * e-mail e escolhe a nova senha.
 */
class ResetPasswordController extends Controller
{
    /**
     * GET /redefinir-senha/{token}?email=...: mostra o formulário.
     * O token vai num campo escondido para ser conferido no envio.
     */
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** POST /redefinir-senha: confere o token e troca a senha. */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ]);

        // Password::reset confere se o token existe, é desse e-mail e não
        // expirou (60 minutos, em config/auth.php). Se estiver tudo certo,
        // roda a função abaixo para salvar a nova senha e apaga o token.
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                // O cast 'hashed' do model cuida do hash.
                $user->forceFill([
                    'password' => $password,
                    // Novo remember_token derruba os "lembrar de mim" antigos.
                    'remember_token' => Str::random(60),
                ])->save();

                // Dispara o evento, caso algum listener queira reagir (ex.: avisar por e-mail).
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return redirect()->route('login')->with('status', __($status));
    }
}
