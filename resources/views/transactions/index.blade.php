<x-layouts.app title="Lançamentos">
    <x-page-header eyebrow="{{ $transactions->total() }} {{ $transactions->total() === 1 ? 'registro' : 'registros' }}" title="Lançamentos">
        <x-slot:actions>
            <a href="{{ route('transactions.create', ['tipo' => 'entrada']) }}" class="btn-ghost border border-rule">+ Entrada</a>
            <a href="{{ route('transactions.create', ['tipo' => 'saida']) }}" class="btn-primary">+ Saída</a>
        </x-slot:actions>
    </x-page-header>

    @if ($transactions->isEmpty())
        <section class="ruled mt-10 rounded-[3px] border border-rule bg-card px-6 py-14 text-center">
            <div class="mx-auto max-w-md bg-card px-6 py-5">
                <p class="font-display text-[26px] italic">Nada anotado ainda.</p>
                <p class="mt-3 text-[15px] text-ink-soft">Comece pelo que entrou ou saiu hoje. Dá para editar depois.</p>
            </div>
        </section>
    @else
        <div class="mt-8 overflow-hidden rounded-[3px] border border-rule bg-card">
            {{-- Cabeçalho só no desktop --}}
            <div class="hidden grid-cols-[90px_1fr_180px_150px_120px] gap-4 border-b border-ink/80 px-5 py-2.5 text-[12px] tracking-wide text-muted uppercase md:grid">
                <span>Data</span><span>Descrição</span><span>Categoria</span><span class="text-right">Valor</span><span></span>
            </div>

            <ul class="divide-y divide-rule">
                @foreach ($transactions as $t)
                    <li class="px-5 py-3.5">
                        {{-- Desktop: uma linha de tabela --}}
                        <div class="hidden grid-cols-[90px_1fr_180px_150px_120px] items-center gap-4 md:grid">
                            <span class="num text-[13px] text-muted">{{ $t->occurred_on->format('d/m/Y') }}</span>
                            <span class="min-w-0">
                                <span class="block truncate text-[15px]">{{ $t->description }}</span>
                                @if ($t->notes)
                                    <span class="block truncate text-[12px] text-muted">{{ $t->notes }}</span>
                                @endif
                            </span>
                            <span class="text-[14px] text-ink-soft">{{ $t->category->name }}</span>
                            <x-amount :transaction="$t" class="text-right text-[15px]" />
                            <span class="flex items-center justify-end gap-4">
                                <a href="{{ route('transactions.edit', $t) }}" class="leading-5 text-[13px] text-ink-soft underline-offset-4 hover:text-ink hover:underline">Editar</a>
                                <x-delete-button :action="route('transactions.destroy', $t)" />
                            </span>
                        </div>

                        {{-- Celular: duas linhas --}}
                        <div class="md:hidden">
                            <div class="flex items-baseline justify-between gap-4">
                                <span class="min-w-0 truncate text-[15px]">{{ $t->description }}</span>
                                <x-amount :transaction="$t" class="text-[15px]" />
                            </div>
                            <div class="mt-1 flex items-center justify-between gap-4">
                                <span class="text-[12px] text-muted"><span class="num">{{ $t->occurred_on->format('d/m') }}</span> · {{ $t->category->name }}</span>
                                <span class="flex items-center gap-4">
                                    <a href="{{ route('transactions.edit', $t) }}" class="leading-5 text-[13px] text-ink-soft">Editar</a>
                                    <x-delete-button :action="route('transactions.destroy', $t)" />
                                </span>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="mt-6">{{ $transactions->links() }}</div>
    @endif
</x-layouts.app>
