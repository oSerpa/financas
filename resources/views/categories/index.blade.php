<x-layouts.app title="Categorias">
    <x-page-header eyebrow="Organização" title="Categorias">
        <x-slot:actions>
            <a href="{{ route('categories.create') }}" class="btn-primary">+ Nova categoria</a>
        </x-slot:actions>
    </x-page-header>

    <div class="mt-8 grid gap-8 md:grid-cols-2">
        @foreach ($groups as $group)
            @php($income = $group['type'] === \App\Enums\TransactionType::Income)
            <section>
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="flex items-center gap-2.5 font-display text-[22px]">
                        <span class="size-2.5 rounded-full {{ $income ? 'bg-income' : 'bg-expense' }}"></span>
                        {{ $group['type']->plural() }}
                    </h2>
                    <a href="{{ route('categories.create', ['tipo' => $group['type']->value]) }}" class="text-[13px] text-ink-soft hover:text-ink">+ adicionar</a>
                </div>

                <ul class="divide-y divide-rule rounded-[3px] border border-rule bg-card">
                    @forelse ($group['items'] as $category)
                        <li class="flex items-center gap-4 px-5 py-3">
                            <span class="flex-1 text-[15px]">{{ $category->name }}</span>
                            <span class="num text-[12px] text-muted">
                                {{ $category->transactions_count }} {{ $category->transactions_count === 1 ? 'lançamento' : 'lançamentos' }}
                            </span>
                            <a href="{{ route('categories.edit', $category) }}" class="leading-5 text-[13px] text-ink-soft underline-offset-4 hover:text-ink hover:underline">Editar</a>
                            <x-delete-button :action="route('categories.destroy', $category)" confirm="Apagar a categoria {{ $category->name }}?" />
                        </li>
                    @empty
                        <li class="px-5 py-6 text-center text-[14px] text-muted">Nenhuma categoria.</li>
                    @endforelse
                </ul>
            </section>
        @endforeach
    </div>
</x-layouts.app>
