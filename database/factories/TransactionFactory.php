<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Fabrica lançamentos falsos. Ex.: Transaction::factory()->count(5)->create().
 *
 * @extends Factory<Transaction>
 */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            // O lançamento pertence ao mesmo usuário da categoria.
            'user_id' => fn (array $attrs) => Category::find($attrs['category_id'])->user_id,
            'description' => fake()->sentence(3),
            // Valor entre 5 e 2000, com 2 casas decimais.
            'amount' => fake()->randomFloat(2, 5, 2000),
            // Data qualquer dos últimos 2 meses.
            'occurred_on' => fake()->dateTimeBetween('-2 months'),
        ];
    }
}
