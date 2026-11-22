{{--
    Formulário de lançamento, o mesmo para criar (GET /lancamentos/novo) e
    editar (GET /lancamentos/{id}/editar). Recebe do TransactionController:
    $transaction, $type (enum do tipo inicial) e $categories (agrupadas por tipo).
--}}
@php
    use App\Enums\TransactionType;
    $editing = $transaction->exists; // true quando veio do banco (edição)
    // O radio se chama _type: começa com _ porque não é salvo, só ajuda a filtrar o select.
    $selectedType = old('_type', $type->value);
    // Mostra o valor no formato brasileiro (1.234,56) na edição.
    $amount = old('amount', $transaction->amount !== null ? number_format((float) $transaction->amount, 2, ',', '.') : '');
@endphp

<x-layouts.app :title="$editing ? 'Editar lançamento' : 'Novo lançamento'">
    <a href="{{ route('transactions.index') }}" class="text-[14px] text-muted hover:text-ink">← Lançamentos</a>

    <div class="mt-4 grid gap-10 lg:grid-cols-[1fr_280px]">
        <form method="POST"
              action="{{ $editing ? route('transactions.update', $transaction) : route('transactions.store') }}"
              class="rounded-[3px] border border-rule bg-card p-6 sm:p-8">
            @csrf
            {{-- Na edição, @method('PUT') faz o POST casar com a rota transactions.update. --}}
            @if ($editing) @method('PUT') @endif

            <h1 class="font-display text-[32px] leading-tight">{{ $editing ? 'Editar lançamento' : 'Novo lançamento' }}</h1>

            {{-- O tipo só filtra as categorias; o que vale é o tipo da categoria escolhida. --}}
            <fieldset class="mt-7">
                <legend class="field-label">Tipo</legend>
                {{-- data-type-toggle e data-category-select são usados pelo resources/js/app.js. --}}
                <div class="inline-grid grid-cols-2 rounded-[3px] border border-rule p-1" data-type-toggle>
                    @foreach (TransactionType::cases() as $case)
                        <label class="cursor-pointer">
                            <input type="radio" name="_type" value="{{ $case->value }}" class="peer sr-only" @checked($selectedType === $case->value)>
                            <span @class([
                                'block rounded-[2px] px-5 py-1.5 text-center text-[14px] text-ink-soft transition peer-focus-visible:ring-2 peer-focus-visible:ring-ink',
                                'peer-checked:bg-income peer-checked:text-paper' => $case === TransactionType::Income,
                                'peer-checked:bg-expense peer-checked:text-paper' => $case === TransactionType::Expense,
                            ])>{{ $case->label() }}</span>
                        </label>
                    @endforeach
                </div>
            </fieldset>

            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="description" class="field-label">Descrição</label>
                    <input id="description" name="description" type="text" class="field-input"
                           value="{{ old('description', $transaction->description) }}" placeholder="Ex.: Conta de luz" required maxlength="120">
                    @error('description') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="amount" class="field-label">Valor</label>
                    <div class="relative">
                        <span class="num pointer-events-none absolute top-1/2 left-3 -translate-y-1/2 text-[14px] text-muted">R$</span>
                        {{-- type="text" (e não number) para aceitar vírgula. inputmode="decimal" abre o teclado numérico no celular. --}}
                        <input id="amount" name="amount" type="text" inputmode="decimal" class="field-input num pl-10"
                               value="{{ $amount }}" placeholder="0,00" required>
                    </div>
                    @error('amount') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="occurred_on" class="field-label">Data</label>
                    {{-- O input date usa o formato AAAA-MM-DD, por isso toDateString(). --}}
                    <input id="occurred_on" name="occurred_on" type="date" class="field-input num"
                           value="{{ old('occurred_on', $transaction->occurred_on?->toDateString()) }}" required>
                    @error('occurred_on') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="category_id" class="field-label">Categoria</label>
                    <select id="category_id" name="category_id" class="field-input" required data-category-select>
                        <option value="">Escolha uma categoria</option>
                        {{-- Um optgroup por tipo. O JS esconde o grupo do tipo que não está marcado. --}}
                        @foreach (TransactionType::cases() as $case)
                            <optgroup label="{{ $case->plural() }}" data-type="{{ $case->value }}">
                                @foreach ($categories->get($case->value, []) as $category)
                                    {{-- (int) porque old() devolve texto e o id é número; o === compara os dois tipos. --}}
                                    <option value="{{ $category->id }}" @selected((int) old('category_id', $transaction->category_id) === $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('category_id') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="notes" class="field-label">Observação <span class="font-normal text-muted">(opcional)</span></label>
                    <textarea id="notes" name="notes" rows="3" class="field-input" maxlength="1000">{{ old('notes', $transaction->notes) }}</textarea>
                    @error('notes') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex items-center gap-3 border-t border-rule pt-6">
                <button type="submit" class="btn-primary">{{ $editing ? 'Salvar alterações' : 'Registrar' }}</button>
                <a href="{{ route('transactions.index') }}" class="btn-ghost">Cancelar</a>
            </div>
        </form>

        <aside class="text-[14px] leading-relaxed text-ink-soft">
            <p class="font-display text-[18px] text-ink italic">Dica</p>
            <p class="mt-2">Pode digitar o valor do jeito que preferir, com vírgula ou ponto: <span class="num">1.234,56</span> funciona.</p>
            <p class="mt-4">Faltou uma categoria? <a href="{{ route('categories.create') }}" class="text-ink underline decoration-highlight decoration-2 underline-offset-4">Crie uma nova</a>.</p>
        </aside>
    </div>
</x-layouts.app>
