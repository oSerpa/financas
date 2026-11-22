<?php

/*
 * Teste unitário: testa só a classe Money, sem banco e sem requisição HTTP.
 * expect(...)->toBe(...) é a forma do Pest de comparar resultados.
 */

use App\Support\Money;

test('formata valores em reais', function (float $value, string $expected) {
    expect(Money::format($value))->toBe($expected);
})->with([
    [0, 'R$ 0,00'],
    [1234.5, 'R$ 1.234,50'],
    [-80, '−R$ 80,00'],
]);

test('interpreta valores digitados', function (string $input, string $expected) {
    expect(Money::parse($input))->toBe($expected);
})->with([
    ['1.234,56', '1234.56'],
    ['1234.56', '1234.56'],
    ['R$ 10,5', '10.5'],
    ['80', '80'],
]);
