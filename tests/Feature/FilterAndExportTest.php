<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->travelTo('2026-11-20');
    $this->user = User::factory()->create();
    $this->salario = Category::factory()->for($this->user)->income()->create(['name' => 'Salário']);
    $this->mercado = Category::factory()->for($this->user)->create(['name' => 'Mercado']);
    $this->lazer = Category::factory()->for($this->user)->create(['name' => 'Lazer']);

    Transaction::factory()->for($this->salario)->create(['description' => 'Salário nov', 'amount' => 3000, 'occurred_on' => '2026-11-05']);
    Transaction::factory()->for($this->mercado)->create(['description' => 'Feira', 'amount' => 450.5, 'occurred_on' => '2026-11-12']);
    Transaction::factory()->for($this->lazer)->create(['description' => 'Cinema', 'amount' => 49.5, 'occurred_on' => '2026-11-15']);
    Transaction::factory()->for($this->mercado)->create(['description' => 'Compra outubro', 'amount' => 999, 'occurred_on' => '2026-10-30']);
});

test('painel soma só o mês escolhido', function () {
    $this->actingAs($this->user)->get('/painel')
        ->assertOk()
        ->assertSee('R$ 3.000,00')
        ->assertSee('R$ 500,00')     // saídas: 450,50 + 49,50
        ->assertSee('R$ 2.500,00');  // saldo

    $this->actingAs($this->user)->get('/painel?mes=2026-10')
        ->assertOk()
        ->assertSee('R$ 999,00')
        ->assertSee('outubro de 2026');
});

test('painel mostra o gasto por categoria com percentual', function () {
    $this->actingAs($this->user)->get('/painel')
        ->assertSeeInOrder(['Para onde foi o dinheiro', 'Mercado', '90,1%', 'Lazer', '9,9%']);
});

test('mês inválido na URL cai no mês atual', function () {
    $this->actingAs($this->user)->get('/painel?mes=2026-13')
        ->assertOk()->assertSee('novembro de 2026');
});

test('lista filtra por mês, tipo, categoria e busca', function () {
    $this->actingAs($this->user)->get('/lancamentos?mes=2026-10')
        ->assertSee('Compra outubro')->assertDontSee('Feira');

    $this->actingAs($this->user)->get('/lancamentos?tipo=entrada')
        ->assertSee('Salário nov')->assertDontSee('Feira');

    $this->actingAs($this->user)->get("/lancamentos?categoria={$this->lazer->id}")
        ->assertSee('Cinema')->assertDontSee('Feira');

    $this->actingAs($this->user)->get('/lancamentos?busca=fei')
        ->assertSee('Feira')->assertDontSee('Cinema');
});

test('exporta CSV do mês com valores no formato brasileiro', function () {
    $response = $this->actingAs($this->user)->get('/lancamentos/exportar?mes=2026-11');

    $response->assertOk()->assertDownload('lancamentos-2026-11.csv');

    $csv = $response->streamedContent();

    expect($csv)
        ->toStartWith("\xEF\xBB\xBF")
        ->toContain('Data;Descrição;Categoria;Tipo;Valor;Observação')
        ->toContain('05/11/2026;"Salário nov";Salário;Entrada;3000,00;')
        ->toContain('12/11/2026;Feira;Mercado;Saída;-450,50;')
        ->not->toContain('Compra outubro');
});

test('exportação não inclui dados de outro usuário', function () {
    Transaction::factory()->create(['description' => 'Segredo alheio', 'occurred_on' => '2026-11-10']);

    $csv = $this->actingAs($this->user)->get('/lancamentos/exportar?mes=2026-11')->streamedContent();

    expect($csv)->not->toContain('Segredo alheio');
});
