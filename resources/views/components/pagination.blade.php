{{--
    Paginação própria, ativada no AppServiceProvider com
    Paginator::defaultView('components.pagination'). É o HTML que aparece
    quando a view chama {{ $transactions->links() }}.

    $paginator é o objeto que o ->paginate(20) do controller devolveu.
--}}
@if ($paginator->hasPages())
    <nav class="flex items-center justify-between text-[14px]" aria-label="Paginação">
        @if ($paginator->onFirstPage())
            <span class="text-muted/60">← Anteriores</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="text-ink-soft hover:text-ink">← Anteriores</a>
        @endif

        <span class="num text-[13px] text-muted">página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="text-ink-soft hover:text-ink">Próximos →</a>
        @else
            <span class="text-muted/60">Próximos →</span>
        @endif
    </nav>
@endif
