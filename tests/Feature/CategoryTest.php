<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_cadastro_cria_categorias_padrao(): void
    {
        $this->post('/cadastro', [
            'name' => 'Maria', 'email' => 'maria@example.com',
            'password' => 'senha-segura', 'password_confirmation' => 'senha-segura',
        ]);

        $user = User::firstWhere('email', 'maria@example.com');
        $this->assertSame(9, $user->categories()->count());
    }

    public function test_lista_apenas_categorias_do_usuario(): void
    {
        $user = User::factory()->create();
        Category::factory()->for($user)->create(['name' => 'Minha']);
        Category::factory()->create(['name' => 'DeOutro']);

        $this->actingAs($user)->get('/categorias')
            ->assertOk()->assertSee('Minha')->assertDontSee('DeOutro');
    }

    public function test_cria_e_edita_categoria(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/categorias', ['name' => 'Academia', 'type' => 'saida'])
            ->assertRedirect('/categorias');

        $category = $user->categories()->firstWhere('name', 'Academia');
        $this->assertNotNull($category);

        $this->actingAs($user)->put("/categorias/{$category->id}", ['name' => 'Esporte', 'type' => 'saida'])
            ->assertRedirect('/categorias');

        $this->assertSame('Esporte', $category->fresh()->name);
    }

    public function test_nome_repetido_no_mesmo_tipo_e_recusado(): void
    {
        $user = User::factory()->create();
        Category::factory()->for($user)->create(['name' => 'Mercado', 'type' => 'saida']);

        $this->actingAs($user)->post('/categorias', ['name' => 'Mercado', 'type' => 'saida'])
            ->assertSessionHasErrors('name');

        // Em outro tipo, pode.
        $this->actingAs($user)->post('/categorias', ['name' => 'Mercado', 'type' => 'entrada'])
            ->assertSessionHasNoErrors();
    }

    public function test_nao_mexe_em_categoria_de_outro_usuario(): void
    {
        $user = User::factory()->create();
        $alheia = Category::factory()->create();

        $this->actingAs($user)->get("/categorias/{$alheia->id}/editar")->assertForbidden();
        $this->actingAs($user)->put("/categorias/{$alheia->id}", ['name' => 'X', 'type' => 'saida'])->assertForbidden();
        $this->actingAs($user)->delete("/categorias/{$alheia->id}")->assertForbidden();
    }

    public function test_nao_apaga_categoria_com_lancamentos(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        Transaction::factory()->for($category)->create();

        $this->actingAs($user)->delete("/categorias/{$category->id}")
            ->assertSessionHas('error');

        $this->assertModelExists($category);
    }

    public function test_apaga_categoria_vazia(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)->delete("/categorias/{$category->id}")->assertRedirect('/categorias');

        $this->assertModelMissing($category);
    }
}
