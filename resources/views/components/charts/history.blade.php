{{--
    Gráfico de barras dos últimos 6 meses. $history vem do
    DashboardController::lastSixMonths() e $current é o mês filtrado ('2026-11').
--}}
@props(['history', 'current'])

@php
    use App\Support\Money;
    // Maior valor entre entradas e saídas: vira a altura de 100%. O 1 evita divisão por zero.
    $max = max(1, $history->max('income'), $history->max('expense'));
    $hasData = $history->contains(fn ($m) => $m['income'] > 0 || $m['expense'] > 0);
@endphp

{{-- Barras agrupadas por mês, um eixo só. Duas séries: legenda sempre visível. --}}
<section class="rounded-[3px] border border-rule bg-card p-6">
    <div class="flex flex-wrap items-baseline justify-between gap-4">
        <h2 class="font-display text-[22px]">Últimos 6 meses</h2>
        <div class="flex gap-4 text-[13px] text-ink-soft">
            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-[2px] bg-chart-income"></span>Entradas</span>
            <span class="flex items-center gap-1.5"><span class="size-2.5 rounded-[2px] bg-chart-expense"></span>Saídas</span>
        </div>
    </div>

    @if (! $hasData)
        <p class="mt-6 text-[14px] text-muted">Ainda não há lançamentos nesse período.</p>
    @else
        {{-- data-chart e data-tip são lidos pelo resources/js/app.js para montar o tooltip. --}}
        <div class="relative mt-6" data-chart>
            <div class="grid h-48 grid-cols-6 items-end gap-3 border-b border-ink/70">
                @foreach ($history as $m)
                    <a href="{{ route('dashboard', ['mes' => $m['key']]) }}"
                       class="relative flex h-full items-end justify-center gap-[2px] rounded-t-[3px] px-1 hover:bg-paper-dark/60"
                       data-tip="{{ ucfirst($m['full']) }}|{{ Money::format($m['income']) }}|{{ Money::format($m['expense']) }}"
                       aria-label="{{ $m['full'] }}: entradas {{ Money::format($m['income']) }}, saídas {{ Money::format($m['expense']) }}">
                        <span class="w-full max-w-5 rounded-t-[4px] bg-chart-income" style="height: {{ $m['income'] / $max * 100 }}%"></span>
                        <span class="w-full max-w-5 rounded-t-[4px] bg-chart-expense" style="height: {{ $m['expense'] / $max * 100 }}%"></span>
                    </a>
                @endforeach
            </div>
            <div class="mt-2 grid grid-cols-6 gap-3">
                @foreach ($history as $m)
                    {{-- @class aplica cada classe só se a condição for verdadeira: o mês atual fica em destaque. --}}
                    <span @class([
                        'num text-center text-[12px] uppercase',
                        'font-medium text-ink' => $m['key'] === $current,
                        'text-muted' => $m['key'] !== $current,
                    ])>{{ rtrim($m['label'], '.') }}</span>
                @endforeach
            </div>

            <div class="pointer-events-none absolute z-10 hidden min-w-44 rounded-[3px] border border-ink bg-card px-3 py-2 text-[13px] shadow-[3px_3px_0_0_var(--color-ink)]" data-tooltip></div>
        </div>

        {{-- Versão em tabela dos mesmos dados, para leitores de tela e para quem prefere números. --}}
        <details class="mt-5 text-[13px]">
            <summary class="cursor-pointer text-ink-soft hover:text-ink">Ver como tabela</summary>
            <table class="num mt-3 w-full text-left">
                <thead class="text-muted"><tr><th class="py-1 font-normal">Mês</th><th class="py-1 text-right font-normal">Entradas</th><th class="py-1 text-right font-normal">Saídas</th><th class="py-1 text-right font-normal">Saldo</th></tr></thead>
                <tbody class="divide-y divide-rule">
                    @foreach ($history as $m)
                        <tr><td class="py-1.5">{{ $m['key'] }}</td><td class="py-1.5 text-right">{{ Money::format($m['income'], false) }}</td><td class="py-1.5 text-right">{{ Money::format($m['expense'], false) }}</td><td class="py-1.5 text-right">{{ Money::format($m['income'] - $m['expense'], false) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </details>
    @endif
</section>
