<?php

namespace Database\Factories;

use App\Enums\TransactionType;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => ucfirst(fake()->unique()->word()),
            'type' => TransactionType::Expense,
        ];
    }

    public function income(): static
    {
        return $this->state(['type' => TransactionType::Income]);
    }
}
