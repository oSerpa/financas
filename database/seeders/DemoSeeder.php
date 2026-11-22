<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Conta de demonstração com 6 meses de lançamentos plausíveis.
 * Login: demo@caderneta.test / senha: caderneta
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Recria a conta do zero. Apaga os lançamentos antes, porque a categoria não pode
        // ser removida enquanto tiver lançamentos (restrictOnDelete).
        if ($old = User::firstWhere('email', 'demo@caderneta.test')) {
            $old->transactions()->delete();
            $old->delete();
        }

        $user = User::create([
            'name' => 'Bruna Lima',
            'email' => 'demo@caderneta.test',
            'password' => 'caderneta',
        ]);
        $user->createDefaultCategories();

        $categories = $user->categories()->pluck('id', 'name');

        // [descrição, categoria, valor mínimo, valor máximo, dia do mês]
        $monthly = [
            ['Salário', 'Salário', 4200, 4200, 5],
            ['Aluguel', 'Moradia', 1350, 1350, 8],
            ['Condomínio', 'Moradia', 380, 380, 10],
            ['Supermercado', 'Mercado', 420, 640, 12],
            ['Feira', 'Mercado', 60, 120, 19],
            ['Combustível', 'Transporte', 150, 260, 15],
            ['Ônibus e metrô', 'Transporte', 40, 90, 3],
            ['Luz', 'Contas', 110, 190, 20],
            ['Internet', 'Contas', 99.9, 99.9, 20],
            ['Celular', 'Contas', 49.9, 49.9, 22],
            ['Farmácia', 'Saúde', 25, 140, 17],
            ['Restaurante', 'Lazer', 60, 220, 14],
            ['Streaming', 'Lazer', 55.9, 55.9, 1],
        ];

        $occasional = [
            ['Projeto freelance', 'Freelance', 600, 1800],
            ['Venda de usado', 'Outras entradas', 80, 400],
            ['Cinema', 'Lazer', 40, 90],
            ['Consulta médica', 'Saúde', 150, 300],
            ['Aplicativo de transporte', 'Transporte', 20, 70],
        ];

        mt_srand(2026); // mesma semente: os dados saem iguais a cada execução

        foreach (range(5, 0) as $monthsAgo) {
            $month = now()->startOfMonth()->subMonths($monthsAgo);
            $lastDay = $monthsAgo === 0 ? now()->day : $month->daysInMonth;

            foreach ($monthly as [$description, $category, $min, $max, $day]) {
                if ($day > $lastDay) {
                    continue;
                }

                $this->add($user, $categories[$category], $description, $min, $max, $month->copy()->day($day));
            }

            foreach ($occasional as [$description, $category, $min, $max]) {
                if (mt_rand(0, 1)) {
                    $this->add($user, $categories[$category], $description, $min, $max, $month->copy()->day(mt_rand(1, $lastDay)));
                }
            }
        }
    }

    private function add(User $user, int $categoryId, string $description, float $min, float $max, $date): void
    {
        $amount = $min === $max ? $min : mt_rand((int) ($min * 100), (int) ($max * 100)) / 100;

        $user->transactions()->create([
            'category_id' => $categoryId,
            'description' => $description,
            'amount' => $amount,
            'occurred_on' => $date,
        ]);
    }
}
