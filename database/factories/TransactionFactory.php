<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Transaction> */
class TransactionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            // O lançamento pertence ao mesmo usuário da categoria.
            'user_id' => fn (array $attrs) => Category::find($attrs['category_id'])->user_id,
            'description' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 5, 2000),
            'occurred_on' => fake()->dateTimeBetween('-2 months'),
        ];
    }
}
