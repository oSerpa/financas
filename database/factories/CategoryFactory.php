<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory: fabrica categorias com dados falsos para testes e seeders.
 * Ex.: Category::factory()->create() ou Category::factory()->income()->for($user)->create().
 *
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    /** Valores padrão de uma categoria fabricada. */
    public function definition(): array
    {
        return [
            // Passar uma factory aqui cria um usuário novo junto, se nenhum for informado.
            'user_id' => User::factory(),
            // fake() usa a biblioteca Faker. unique() evita nomes repetidos na mesma execução.
            'name' => ucfirst(fake()->unique()->word()),
            'type' => TransactionType::Expense,
        ];
    }

    /** Estado alternativo: Category::factory()->income() cria uma categoria de entrada. */
    public function income(): static
    {
        return $this->state(['type' => TransactionType::Income]);
    }
}
