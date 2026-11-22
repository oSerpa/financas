<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder principal, executado por php artisan db:seed (ou migrate --seed).
 * Só chama os outros seeders.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DemoSeeder::class);
    }
}
