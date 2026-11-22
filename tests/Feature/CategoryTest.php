<?php

/*
 * Testes do CRUD de categorias, incluindo a regra de nome único e o
 * bloqueio de acesso às categorias de outro usuário.
 *
 * beforeEach roda antes de cada teste e cria um usuário novo em $this->user.
 * Category::factory()->for($user) cria a categoria já ligada a esse usuário.
 */

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('lista apenas categorias do usuário', function () {
    Category::factory()->for($this->user)->create(['name' => 'Minha']);
    Category::factory()->create(['name' => 'DeOutro']);

    $this->actingAs($this->user)->get('/categorias')
        ->assertOk()->assertSee('Minha')->assertDontSee('DeOutro');
});

test('cria e edita categoria', function () {
    $this->actingAs($this->user)->post('/categorias', ['name' => 'Academia', 'type' => 'saida'])
        ->assertRedirect('/categorias');

    $category = $this->user->categories()->firstWhere('name', 'Academia');
    expect($category)->not->toBeNull();

    $this->actingAs($this->user)->put("/categorias/{$category->id}", ['name' => 'Esporte', 'type' => 'saida'])
        ->assertRedirect('/categorias');

    expect($category->fresh()->name)->toBe('Esporte');
});

test('nome repetido no mesmo tipo é recusado, em outro tipo pode', function () {
    Category::factory()->for($this->user)->create(['name' => 'Mercado', 'type' => 'saida']);

    $this->actingAs($this->user)->post('/categorias', ['name' => 'Mercado', 'type' => 'saida'])
        ->assertSessionHasErrors('name');

    $this->actingAs($this->user)->post('/categorias', ['name' => 'Mercado', 'type' => 'entrada'])
        ->assertSessionHasNoErrors();
});

test('não mexe em categoria de outro usuário', function () {
    $alheia = Category::factory()->create();

    $this->actingAs($this->user)->get("/categorias/{$alheia->id}/editar")->assertForbidden();
    $this->actingAs($this->user)->put("/categorias/{$alheia->id}", ['name' => 'X', 'type' => 'saida'])->assertForbidden();
    $this->actingAs($this->user)->delete("/categorias/{$alheia->id}")->assertForbidden();
});

test('não apaga categoria com lançamentos', function () {
    $category = Category::factory()->for($this->user)->create();
    Transaction::factory()->for($category)->create();

    $this->actingAs($this->user)->delete("/categorias/{$category->id}")->assertSessionHas('error');

    $this->assertModelExists($category);
});

test('apaga categoria vazia', function () {
    $category = Category::factory()->for($this->user)->create();

    $this->actingAs($this->user)->delete("/categorias/{$category->id}")->assertRedirect('/categorias');

    $this->assertModelMissing($category);
});
