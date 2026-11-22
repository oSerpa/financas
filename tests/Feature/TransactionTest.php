<?php

/*
 * Testes do CRUD de lançamentos: criar com valor no formato brasileiro,
 * editar, apagar e impedir acesso aos lançamentos de outro usuário (403).
 */

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->travelTo('2026-11-20');
    $this->user = User::factory()->create();
    $this->category = Category::factory()->for($this->user)->create(['name' => 'Mercado']);
});

test('registra lançamento com valor no formato brasileiro', function () {
    $this->actingAs($this->user)->post('/lancamentos', [
        'description' => 'Conta de luz',
        'amount' => '1.234,56',
        'occurred_on' => '2026-11-10',
        'category_id' => $this->category->id,
    ])->assertRedirect('/lancamentos?mes=2026-11');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $this->user->id,
        'description' => 'Conta de luz',
        'amount' => '1234.56',
    ]);
});

test('valida campos obrigatórios e valor positivo', function () {
    $this->actingAs($this->user)->post('/lancamentos', ['amount' => '0'])
        ->assertSessionHasErrors(['description', 'amount', 'occurred_on', 'category_id']);
});

test('não aceita categoria de outro usuário', function () {
    $alheia = Category::factory()->create();

    $this->actingAs($this->user)->post('/lancamentos', [
        'description' => 'Teste', 'amount' => '10', 'occurred_on' => '2026-11-10',
        'category_id' => $alheia->id,
    ])->assertSessionHasErrors('category_id');
});

test('lista apenas lançamentos do usuário', function () {
    Transaction::factory()->for($this->category)->create(['description' => 'Meu gasto', 'occurred_on' => '2026-11-02']);
    Transaction::factory()->create(['description' => 'Gasto alheio', 'occurred_on' => '2026-11-02']);

    $this->actingAs($this->user)->get('/lancamentos')
        ->assertOk()->assertSee('Meu gasto')->assertDontSee('Gasto alheio');
});

test('edita e apaga lançamento', function () {
    $t = Transaction::factory()->for($this->category)->create();

    $this->actingAs($this->user)->get("/lancamentos/{$t->id}/editar")->assertOk();

    $this->actingAs($this->user)->put("/lancamentos/{$t->id}", [
        'description' => 'Corrigido', 'amount' => '50,00', 'occurred_on' => '2026-11-01',
        'category_id' => $this->category->id,
    ])->assertRedirect('/lancamentos?mes=2026-11');

    expect($t->fresh()->description)->toBe('Corrigido');

    $this->actingAs($this->user)->delete("/lancamentos/{$t->id}");
    $this->assertModelMissing($t);
});

test('não mexe em lançamento de outro usuário', function () {
    $alheio = Transaction::factory()->create();

    $this->actingAs($this->user)->get("/lancamentos/{$alheio->id}/editar")->assertForbidden();
    $this->actingAs($this->user)->delete("/lancamentos/{$alheio->id}")->assertForbidden();
    $this->assertModelExists($alheio);
});
