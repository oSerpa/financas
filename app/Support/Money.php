<?php

namespace App\Support;

/**
 * Formata e interpreta valores em reais sem depender da extensão intl.
 */
class Money
{
    public static function format(float|int|string|null $value, bool $withSymbol = true): string
    {
        $formatted = number_format(abs((float) $value), 2, ',', '.');
        $sign = (float) $value < 0 ? '−' : '';

        return $sign.($withSymbol ? 'R$ ' : '').$formatted;
    }

    /**
     * Converte "1.234,56" ou "1234.56" em "1234.56".
     * Devolve o texto original quando não reconhece o formato, para a validação recusar.
     */
    public static function parse(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(str_replace(['R$', ' '], '', $value));

        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);
            $value = str_replace(',', '.', $value);
        }

        return $value;
    }
}
