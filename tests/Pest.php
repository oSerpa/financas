<?php

/*
 * Configuração do Pest. Todos os testes da pasta Feature usam a TestCase do
 * Laravel (que dá $this->get(), $this->post(), actingAs()...) e o
 * RefreshDatabase, que recria o banco a cada teste. Assim um teste nunca
 * enxerga os dados de outro.
 *
 * Os testes usam SQLite em memória (ver phpunit.xml), então não mexem no
 * banco de verdade. Rodar: php artisan test
 */

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');
