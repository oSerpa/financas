{{--
    Formulário de categoria, o mesmo para criar e editar.
    $category->exists é false para uma Category nova (create) e true para
    uma que veio do banco (edit). Isso decide título, rota e método.
--}}
@php
    use App\Enums\TransactionType;
    $editing = $category->exists;
    // old() tem prioridade (valor reenviado após erro). Senão usa o da categoria.
    // ?->value lida com o tipo vir como enum (do banco) ou como texto (do ?tipo=).
    $selectedType = old('type', $category->type?->value ?? $category->type);
@endphp

<x-layouts.app :title="$editing ? 'Editar categoria' : 'Nova categoria'">
    <a href="{{ route('categories.index') }}" class="text-[14px] text-muted hover:text-ink">← Categorias</a>

    <form method="POST"
          action="{{ $editing ? route('categories.update', $category) : route('categories.store') }}"
          class="mt-4 max-w-lg rounded-[3px] border border-rule bg-card p-6 sm:p-8">
        @csrf
        {{-- Na edição o formulário vira PUT para casar com a rota categories.update. --}}
        @if ($editing) @method('PUT') @endif

        <h1 class="font-display text-[32px] leading-tight">{{ $editing ? 'Editar categoria' : 'Nova categoria' }}</h1>

        <fieldset class="mt-7">
            <legend class="field-label">Tipo</legend>
            <div class="inline-grid grid-cols-2 rounded-[3px] border border-rule p-1">
                {{-- Um radio por caso do enum. O radio fica escondido (sr-only) e o span estilizado
                     muda de cor com peer-checked do Tailwind quando ele está marcado. --}}
                @foreach (TransactionType::cases() as $case)
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="{{ $case->value }}" class="peer sr-only" @checked($selectedType === $case->value)>
                        <span @class([
                            'block rounded-[2px] px-5 py-1.5 text-center text-[14px] text-ink-soft transition peer-focus-visible:ring-2 peer-focus-visible:ring-ink',
                            'peer-checked:bg-income peer-checked:text-paper' => $case === TransactionType::Income,
                            'peer-checked:bg-expense peer-checked:text-paper' => $case === TransactionType::Expense,
                        ])>{{ $case->label() }}</span>
                    </label>
                @endforeach
            </div>
            @error('type') <p class="field-error">{{ $message }}</p> @enderror
        </fieldset>

        <div class="mt-6">
            <label for="name" class="field-label">Nome</label>
            <input id="name" name="name" type="text" class="field-input" maxlength="60" required autofocus
                   value="{{ old('name', $category->name) }}" placeholder="Ex.: Academia">
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div class="mt-8 flex items-center gap-3 border-t border-rule pt-6">
            <button type="submit" class="btn-primary">{{ $editing ? 'Salvar alterações' : 'Criar categoria' }}</button>
            <a href="{{ route('categories.index') }}" class="btn-ghost">Cancelar</a>
        </div>
    </form>
</x-layouts.app>
