<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Primeiro passo do "esqueci minha senha": pede o e-mail e envia o link.
 */
class ForgotPasswordController extends Controller
{
    /** GET /esqueci-senha: formulário com o campo de e-mail. */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /** POST /esqueci-senha: gera o token e manda o e-mail. */
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        // O Laravel gera um token, grava o hash dele na tabela
        // password_reset_tokens e envia o e-mail pelo SMTP do .env (MAIL_*).
        // Devolve um código de status, como Password::RESET_LINK_SENT.
        $status = Password::sendResetLink($request->only('email'));

        // Só o limite de pedidos vira erro. Para e-mail inexistente a resposta é a mesma
        // de sucesso, assim ninguém descobre quais e-mails têm conta.
        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages(['email' => __($status)]);
        }

        return back()->with('status', __(Password::RESET_LINK_SENT));
    }
}
