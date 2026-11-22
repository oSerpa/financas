{{--
    Lista de lançamentos (GET /lancamentos). Recebe do TransactionController@index:
    $transactions (paginado), $filter (MonthFilter), $income, $expense e $categories.
--}}
@php
    use App\Support\Money;
    // Filtros atuais da URL, repassados para a navegação de mês e para a exportação.
    $params = request()->only(['tipo', 'categoria', 'busca']);
    $hasFilters = collect($params)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();
@endphp

<x-layouts.app title="Lançamentos">
    <x-page-header eyebrow="Registro" title="Lançamentos">
        <x-slot:actions>
            <a href="{{ route('transactions.create', ['tipo' => 'entrada']) }}" class="btn-ghost border border-rule">+ Entrada</a>
            <a href="{{ route('transactions.create', ['tipo' => 'saida']) }}" class="btn-primary">+ Saída</a>
        </x-slot:actions>
    </x-page-header>

    {{-- Filtros numa linha só, acima da lista --}}
    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <x-month-nav :filter="$filter" route="transactions.index" :params="$params" />

        {{-- Formulário GET: os campos viram ?busca=...&tipo=... na URL, então o filtro
             pode ser salvo nos favoritos ou compartilhado. O campo escondido mantém o mês. --}}
        <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="mes" value="{{ $filter->key() }}">
            <label class="sr-only" for="busca">Buscar</label>
            <input id="busca" type="search" name="busca" value="{{ request('busca') }}" placeholder="Buscar descrição"
                   class="field-input w-44 py-1.5 text-[14px]">
            <label class="sr-only" for="tipo">Tipo</label>
            {{-- onchange envia o formulário ao trocar a opção, sem precisar clicar em Filtrar. --}}
            <select id="tipo" name="tipo" class="field-input w-auto py-1.5 text-[14px]" onchange="this.form.submit()">
                <option value="">Entradas e saídas</option>
                @foreach (\App\Enums\TransactionType::cases() as $case)
                    <option value="{{ $case->value }}" @selected(request('tipo') === $case->value)>Só {{ mb_strtolower($case->plural()) }}</option>
                @endforeach
            </select>
            <label class="sr-only" for="categoria">Categoria</label>
            <select id="categoria" name="categoria" class="field-input w-auto py-1.5 text-[14px]" onchange="this.form.submit()">
                <option value="">Todas as categorias</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((int) request('categoria') === $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-ghost border border-rule py-1.5">Filtrar</button>
            @if ($hasFilters)
                <a href="{{ route('transactions.index', ['mes' => $filter->key()]) }}" class="text-[13px] text-ink-soft underline decoration-highlight decoration-2 underline-offset-4">Limpar</a>
            @endif
        </form>
    </div>

    <div class="mt-5 flex flex-wrap items-center justify-between gap-4 border-y border-rule py-3 text-[14px]">
        <div class="num flex flex-wrap gap-x-6 gap-y-1">
            {{-- total() é o total de registros do filtro, não só os desta página. --}}
            <span class="text-muted">{{ $transactions->total() }} {{ $transactions->total() === 1 ? 'registro' : 'registros' }}</span>
            <span class="text-income">+ {{ Money::format($income, false) }}</span>
            <span class="text-expense">− {{ Money::format($expense, false) }}</span>
            <span>= {{ Money::format($income - $expense) }}</span>
        </div>
        <a href="{{ route('transactions.export', array_merge($params, ['mes' => $filter->key()])) }}"
           class="text-[14px] text-ink-soft underline decoration-highlight decoration-2 underline-offset-4 hover:text-ink">Exportar CSV</a>
    </div>

    @if ($transactions->isEmpty())
        <section class="ruled mt-10 rounded-[3px] border border-rule bg-card px-6 py-14 text-center">
            <div class="mx-auto max-w-md bg-card px-6 py-5">
                <p class="font-display text-[26px] italic">{{ $hasFilters ? 'Nada encontrado.' : 'Nada anotado neste mês.' }}</p>
                <p class="mt-3 text-[15px] text-ink-soft">{{ $hasFilters ? 'Tente outro filtro ou limpe a busca.' : 'Comece pelo que entrou ou saiu hoje. Dá para editar depois.' }}</p>
            </div>
        </section>
    @else
        <div class="mt-6 overflow-hidden rounded-[3px] border border-rule bg-card">
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

        {{-- Links de página, usando o componente components/pagination.blade.php. --}}
        <div class="mt-6">{{ $transactions->links() }}</div>
    @endif
</x-layouts.app>
