<?php

use App\Models\User;

test('visitante é redirecionado para o login', function () {
    $this->get('/painel')->assertRedirect('/entrar');
});

test('telas de login e cadastro abrem', function () {
    $this->get('/entrar')->assertOk()->assertSee('Bom te ver de novo.');
    $this->get('/cadastro')->assertOk()->assertSee('Abra sua caderneta.');
});

test('usuário consegue se cadastrar e ganha categorias padrão', function () {
    $this->post('/cadastro', [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'password' => 'senha-segura',
        'password_confirmation' => 'senha-segura',
    ])->assertRedirect('/painel');

    $this->assertAuthenticated();
    expect(User::firstWhere('email', 'maria@example.com')->categories()->count())->toBe(9);
});

test('cadastro rejeita e-mail repetido', function () {
    User::factory()->create(['email' => 'maria@example.com']);

    $this->post('/cadastro', [
        'name' => 'Outra Maria',
        'email' => 'maria@example.com',
        'password' => 'senha-segura',
        'password_confirmation' => 'senha-segura',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('usuário consegue entrar e ver o painel', function () {
    $user = User::factory()->create(['name' => 'Maria Silva']);

    $this->post('/entrar', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect('/painel');

    $this->get('/painel')->assertOk()->assertSee('Olá, Maria.');
});

test('senha errada não autentica', function () {
    $user = User::factory()->create();

    $this->post('/entrar', ['email' => $user->email, 'password' => 'errada'])
        ->assertSessionHasErrors(['email' => 'E-mail ou senha incorretos.']);

    $this->assertGuest();
});

test('usuário consegue sair', function () {
    $this->actingAs(User::factory()->create())
        ->post('/sair')
        ->assertRedirect('/entrar');

    $this->assertGuest();
});
