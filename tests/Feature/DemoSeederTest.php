<?php

use App\Models\User;
use Database\Seeders\DemoSeeder;

test('seeder cria a conta demo com lançamentos', function () {
    $this->seed(DemoSeeder::class);

    $user = User::firstWhere('email', 'demo@caderneta.test');

    expect($user)->not->toBeNull()
        ->and($user->categories()->count())->toBe(9)
        ->and($user->transactions()->count())->toBeGreaterThan(50);

    $this->post('/entrar', ['email' => 'demo@caderneta.test', 'password' => 'caderneta'])
        ->assertRedirect('/painel');
});

test('seeder pode rodar de novo sem duplicar a conta', function () {
    $this->seed(DemoSeeder::class);
    $this->seed(DemoSeeder::class);

    expect(User::where('email', 'demo@caderneta.test')->count())->toBe(1);
});
