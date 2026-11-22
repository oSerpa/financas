<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registra_lancamento_com_valor_no_formato_brasileiro(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();

        $this->actingAs($user)->post('/lancamentos', [
            'description' => 'Conta de luz',
            'amount' => '1.234,56',
            'occurred_on' => '2026-11-10',
            'category_id' => $category->id,
        ])->assertRedirect('/lancamentos');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'description' => 'Conta de luz',
            'amount' => '1234.56',
        ]);
    }

    public function test_valida_campos_obrigatorios_e_valor_positivo(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/lancamentos', ['amount' => '0'])
            ->assertSessionHasErrors(['description', 'amount', 'occurred_on', 'category_id']);
    }

    public function test_nao_aceita_categoria_de_outro_usuario(): void
    {
        $user = User::factory()->create();
        $alheia = Category::factory()->create();

        $this->actingAs($user)->post('/lancamentos', [
            'description' => 'Teste', 'amount' => '10', 'occurred_on' => '2026-11-10',
            'category_id' => $alheia->id,
        ])->assertSessionHasErrors('category_id');
    }

    public function test_lista_apenas_lancamentos_do_usuario(): void
    {
        $user = User::factory()->create();
        Transaction::factory()->for(Category::factory()->for($user))->create(['description' => 'Meu gasto']);
        Transaction::factory()->create(['description' => 'Gasto alheio']);

        $this->actingAs($user)->get('/lancamentos')
            ->assertOk()->assertSee('Meu gasto')->assertDontSee('Gasto alheio');
    }

    public function test_edita_e_apaga_lancamento(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->for($user)->create();
        $t = Transaction::factory()->for($category)->create();

        $this->actingAs($user)->get("/lancamentos/{$t->id}/editar")->assertOk();

        $this->actingAs($user)->put("/lancamentos/{$t->id}", [
            'description' => 'Corrigido', 'amount' => '50,00', 'occurred_on' => '2026-11-01',
            'category_id' => $category->id,
        ])->assertRedirect('/lancamentos');

        $this->assertSame('Corrigido', $t->fresh()->description);

        $this->actingAs($user)->delete("/lancamentos/{$t->id}");
        $this->assertModelMissing($t);
    }

    public function test_nao_mexe_em_lancamento_de_outro_usuario(): void
    {
        $user = User::factory()->create();
        $alheio = Transaction::factory()->create();

        $this->actingAs($user)->get("/lancamentos/{$alheio->id}/editar")->assertForbidden();
        $this->actingAs($user)->delete("/lancamentos/{$alheio->id}")->assertForbidden();
        $this->assertModelExists($alheio);
    }

    public function test_painel_soma_entradas_e_saidas_do_mes(): void
    {
        $this->travelTo('2026-11-20');

        $user = User::factory()->create();
        $salario = Category::factory()->for($user)->income()->create();
        $mercado = Category::factory()->for($user)->create();

        Transaction::factory()->for($salario)->create(['amount' => 3000, 'occurred_on' => '2026-11-05']);
        Transaction::factory()->for($mercado)->create(['amount' => 450.5, 'occurred_on' => '2026-11-12']);
        // Mês anterior não entra na conta.
        Transaction::factory()->for($mercado)->create(['amount' => 999, 'occurred_on' => '2026-10-30']);

        $this->actingAs($user)->get('/painel')
            ->assertOk()
            ->assertSee('R$ 3.000,00')
            ->assertSee('R$ 450,50')
            ->assertSee('R$ 2.549,50');
    }
}
