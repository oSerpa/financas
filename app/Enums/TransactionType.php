<?php

namespace App\Enums;

enum TransactionType: string
{
    case Income = 'entrada';
    case Expense = 'saida';

    public function label(): string
    {
        return match ($this) {
            self::Income => 'Entrada',
            self::Expense => 'Saída',
        };
    }

    public function plural(): string
    {
        return match ($this) {
            self::Income => 'Entradas',
            self::Expense => 'Saídas',
        };
    }
}
