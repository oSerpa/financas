{{--
    Gráfico "Para onde foi o dinheiro", feito só com HTML e CSS (sem biblioteca).
    $items vem do DashboardController::expensesByCategory() e $total é a soma das saídas.
--}}
@props(['items', 'total'])

@php use App\Support\Money; @endphp

{{-- Barras horizontais: uma série só (saídas), então não precisa de legenda. --}}
<section class="rounded-[3px] border border-rule bg-card p-6">
    <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1">
        <h2 class="font-display text-[22px]">Para onde foi o dinheiro</h2>
        <span class="num text-[13px] text-muted">{{ Money::format($total) }} em saídas</span>
    </div>

    @if ($items->isEmpty())
        <p class="mt-6 text-[14px] text-muted">Nenhuma saída neste mês.</p>
    @else
        {{-- A maior categoria ocupa 100% da largura e as outras são proporcionais a ela. --}}
        @php $max = $items->max('total'); @endphp
        <ul class="mt-6 space-y-4">
            @foreach ($items as $item)
                <li class="group" title="{{ $item['name'] }}: {{ Money::format($item['total']) }} ({{ number_format($item['percent'], 1, ',', '') }}% das saídas)">
                    <div class="flex items-baseline justify-between gap-3 text-[14px]">
                        <span class="text-ink">{{ $item['name'] }}</span>
                        <span class="num text-ink-soft">
                            {{ Money::format($item['total']) }}
                            <span class="ml-1.5 inline-block w-12 text-right text-muted">{{ number_format($item['percent'], 1, ',', '') }}%</span>
                        </span>
                    </div>
                    <div class="mt-1.5 h-2 rounded-r-[4px] bg-paper-dark">
                        {{-- max(1, ...) garante uma barrinha visível mesmo para valores muito pequenos. --}}
                        <div class="h-2 rounded-r-[4px] bg-chart-expense transition-opacity group-hover:opacity-80"
                             style="width: {{ max(1, $item['total'] / $max * 100) }}%"></div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</section>
