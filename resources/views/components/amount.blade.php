@props(['transaction'])

@php($income = $transaction->type() === \App\Enums\TransactionType::Income)

<span {{ $attributes->merge(['class' => 'num whitespace-nowrap '.($income ? 'text-income' : 'text-expense')]) }}>
    {{ $income ? '+' : '−' }} {{ \App\Support\Money::format($transaction->amount, false) }}
</span>
