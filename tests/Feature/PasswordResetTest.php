<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

test('login mostra o link de esqueci minha senha', function () {
    $this->get('/entrar')->assertSee('Esqueci minha senha')->assertSee('/esqueci-senha');
    $this->get('/esqueci-senha')->assertOk()->assertSee('Esqueceu a senha?');
});

test('pedido de link envia e-mail para quem tem conta', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/esqueci-senha', ['email' => $user->email])
        ->assertRedirect()
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

test('e-mail sem conta recebe a mesma resposta e nada é enviado', function () {
    Notification::fake();

    $this->post('/esqueci-senha', ['email' => 'ninguem@example.com'])
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status', __('passwords.sent'));

    Notification::assertNothingSent();
});

test('e-mail de recuperação sai em português e aponta para a tela certa', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/esqueci-senha', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        $mail = $notification->toMail($user);

        expect($mail->subject)->toBe('Redefinir sua senha')
            ->and($mail->actionText)->toBe('Criar nova senha')
            ->and($mail->actionUrl)->toContain('/redefinir-senha/'.$notification->token);

        $this->get('/redefinir-senha/'.$notification->token.'?email='.urlencode($user->email))
            ->assertOk()
            ->assertSee('Crie uma nova senha.');

        return true;
    });
});

test('senha é redefinida com token válido', function () {
    Notification::fake();
    $user = User::factory()->create();

    $this->post('/esqueci-senha', ['email' => $user->email]);

    Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
        $this->post('/redefinir-senha', [
            'token' => $notification->token,
            'email' => $user->email,
            'password' => 'nova-senha-123',
            'password_confirmation' => 'nova-senha-123',
        ])->assertRedirect('/entrar')->assertSessionHasNoErrors();

        expect(Hash::check('nova-senha-123', $user->fresh()->password))->toBeTrue();

        return true;
    });
});

test('token inválido não troca a senha', function () {
    $user = User::factory()->create();
    $hashAntes = $user->password;

    $this->post('/redefinir-senha', [
        'token' => 'token-falso',
        'email' => $user->email,
        'password' => 'nova-senha-123',
        'password_confirmation' => 'nova-senha-123',
    ])->assertSessionHasErrors('email');

    expect($user->fresh()->password)->toBe($hashAntes);
});
