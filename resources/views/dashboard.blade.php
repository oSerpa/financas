@php
    use App\Support\Money;
    $primeiroNome = strtok(auth()->user()->name, ' ');
@endphp

<x-layouts.app title="Painel">
    <x-page-header :eyebrow="now()->locale('pt_BR')->translatedFormat('l, j \d\e F')" :title="'Olá, '.$primeiroNome.'.'">
        <x-slot:actions>
            <a href="{{ route('transactions.create', ['tipo' => 'entrada']) }}" class="btn-ghost border border-rule">+ Entrada</a>
            <a href="{{ route('transactions.create', ['tipo' => 'saida']) }}" class="btn-primary">+ Saída</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-6">
        <x-month-nav :filter="$filter" route="dashboard" />
    </div>

    <section class="mt-6 grid gap-px overflow-hidden rounded-[3px] border border-rule bg-rule sm:grid-cols-3">
        <div class="bg-card p-6">
            <p class="text-[13px] text-muted">Entradas</p>
            <p class="num mt-2 text-[28px] text-income">{{ Money::format($income) }}</p>
        </div>
        <div class="bg-card p-6">
            <p class="text-[13px] text-muted">Saídas</p>
            <p class="num mt-2 text-[28px] text-expense">{{ Money::format($expense) }}</p>
        </div>
        <div class="bg-card p-6">
            <p class="text-[13px] text-muted">Saldo</p>
            <p class="num mt-2 text-[28px]">
                <span @class(['border-b-2 border-highlight' => $balance != 0])>{{ Money::format($balance) }}</span>
            </p>
        </div>
    </section>

    @if ($latest->isEmpty() && $history->every(fn ($m) => $m['income'] == 0 && $m['expense'] == 0))
        <section class="ruled mt-10 rounded-[3px] border border-rule bg-card px-6 py-14 text-center">
            <div class="mx-auto max-w-md bg-card px-6 py-5">
                <p class="font-display text-[26px] italic">Página em branco, por enquanto.</p>
                <p class="mt-3 text-[15px] text-ink-soft">
                    Registre sua primeira entrada ou saída e o resumo do mês começa a se preencher.
                </p>
            </div>
        </section>
    @else
        <div class="mt-8 grid gap-6 lg:grid-cols-2">
            <x-charts.by-category :items="$byCategory" :total="$expense" />
            <x-charts.history :history="$history" :current="$filter->key()" />
        </div>

        <section class="mt-10">
            <div class="mb-3 flex items-baseline justify-between gap-4">
                <h2 class="font-display text-[22px]">Últimos lançamentos do mês</h2>
                <a href="{{ route('transactions.index', ['mes' => $filter->key()]) }}" class="whitespace-nowrap text-[14px] text-ink-soft underline decoration-highlight decoration-2 underline-offset-4 hover:text-ink">Ver todos</a>
            </div>
            @if ($latest->isEmpty())
                <p class="rounded-[3px] border border-rule bg-card px-5 py-6 text-[14px] text-muted">Nenhum lançamento em {{ $filter->label() }}.</p>
            @else
                <ul class="divide-y divide-rule rounded-[3px] border border-rule bg-card">
                    @foreach ($latest as $t)
                        <li class="flex items-center gap-4 px-5 py-3.5">
                            <span class="num w-12 shrink-0 text-[13px] text-muted">{{ $t->occurred_on->format('d/m') }}</span>
                            <span class="min-w-0 flex-1">
                                <span class="block truncate text-[15px]">{{ $t->description }}</span>
                                <span class="text-[12px] text-muted">{{ $t->category->name }}</span>
                            </span>
                            <x-amount :transaction="$t" class="text-[15px]" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    @endif
</x-layouts.app>
