{{--
    Componente <x-amount :transaction="$t" />: mostra o valor com sinal e cor
    (verde com + para entrada, vermelho com − para saída).

    @props declara os atributos que o componente recebe. Os outros atributos
    (como class="...") ficam em $attributes e são juntados com merge().
--}}
@props(['transaction'])

{{-- @php(...) executa uma linha de PHP dentro da view. --}}
@php($income = $transaction->type() === \App\Enums\TransactionType::Income)

<span {{ $attributes->merge(['class' => 'num whitespace-nowrap '.($income ? 'text-income' : 'text-expense')]) }}>
    {{ $income ? '+' : '−' }} {{ \App\Support\Money::format($transaction->amount, false) }}
</span>
