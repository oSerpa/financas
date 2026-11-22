<?php

namespace App\Enums;

/**
 * Tipos possíveis de lançamento. É um "backed enum": cada caso tem um
 * valor em texto, que é o que fica gravado no banco.
 *
 * Usar enum em vez de strings soltas evita erro de digitação ('saída'
 * com acento, por exemplo) e deixa o editor sugerir os valores.
 *
 * Exemplos: TransactionType::Income->value === 'entrada'
 *           TransactionType::from('saida') === TransactionType::Expense
 */
enum TransactionType: string
{
    case Income = 'entrada';
    case Expense = 'saida';

    /** Nome para mostrar na tela, no singular. */
    public function label(): string
    {
        // match é como um switch que devolve valor e não precisa de break.
        return match ($this) {
            self::Income => 'Entrada',
            self::Expense => 'Saída',
        };
    }

    /** Nome no plural, usado em títulos e nos cards do painel. */
    public function plural(): string
    {
        return match ($this) {
            self::Income => 'Entradas',
            self::Expense => 'Saídas',
        };
    }
}
