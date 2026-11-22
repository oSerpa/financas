<?php

namespace App\Support;

/**
 * Formata e interpreta valores em reais sem depender da extensão intl.
 *
 * Métodos estáticos: chamados direto na classe, sem new.
 * Ex.: Money::format(1234.5) devolve "R$ 1.234,50".
 */
class Money
{
    /**
     * Transforma um número no formato brasileiro.
     *
     * @param  bool  $withSymbol  false devolve só "1.234,50", sem o "R$ ".
     */
    public static function format(float|int|string|null $value, bool $withSymbol = true): string
    {
        // number_format(valor, casas, separador decimal, separador de milhar)
        $formatted = number_format(abs((float) $value), 2, ',', '.');
        // Usa o sinal de menos tipográfico (−), que fica melhor na tela que o hífen.
        $sign = (float) $value < 0 ? '−' : '';

        return $sign.($withSymbol ? 'R$ ' : '').$formatted;
    }

    /**
     * Converte "1.234,56" ou "1234.56" em "1234.56".
     * Devolve o texto original quando não reconhece o formato, para a validação recusar.
     *
     * Chamado no prepareForValidation() do TransactionRequest, antes das regras rodarem.
     */
    public static function parse(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Tira "R$" e espaços: "R$ 1.234,56" vira "1.234,56".
        $value = trim(str_replace(['R$', ' '], '', $value));

        // Se tem vírgula, é formato brasileiro: o ponto é milhar e a vírgula é decimal.
        if (str_contains($value, ',')) {
            $value = str_replace('.', '', $value);  // "1.234,56" vira "1234,56"
            $value = str_replace(',', '.', $value); // "1234,56" vira "1234.56"
        }

        return $value;
    }
}
