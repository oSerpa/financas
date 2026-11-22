<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitante_e_redirecionado_para_o_login(): void
    {
        $this->get('/painel')->assertRedirect('/entrar');
    }

    public function test_telas_de_login_e_cadastro_abrem(): void
    {
        $this->get('/entrar')->assertOk()->assertSee('Bom te ver de novo.');
        $this->get('/cadastro')->assertOk()->assertSee('Abra sua caderneta.');
    }

    public function test_usuario_consegue_se_cadastrar(): void
    {
        $response = $this->post('/cadastro', [
            'name' => 'Maria Silva',
            'email' => 'maria@example.com',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ]);

        $response->assertRedirect('/painel');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'maria@example.com']);
    }

    public function test_cadastro_rejeita_email_repetido(): void
    {
        User::factory()->create(['email' => 'maria@example.com']);

        $this->post('/cadastro', [
            'name' => 'Outra Maria',
            'email' => 'maria@example.com',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_usuario_consegue_entrar_e_ver_o_painel(): void
    {
        $user = User::factory()->create(['name' => 'Maria Silva']);

        $this->post('/entrar', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/painel');

        $this->get('/painel')->assertOk()->assertSee('Olá, Maria.');
    }

    public function test_senha_errada_nao_autentica(): void
    {
        $user = User::factory()->create();

        $this->post('/entrar', ['email' => $user->email, 'password' => 'errada'])
            ->assertSessionHasErrors(['email' => 'E-mail ou senha incorretos.']);

        $this->assertGuest();
    }

    public function test_usuario_consegue_sair(): void
    {
        $this->actingAs(User::factory()->create())
            ->post('/sair')
            ->assertRedirect('/entrar');

        $this->assertGuest();
    }
}
