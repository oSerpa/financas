@props(['filter', 'route', 'params' => []])

{{--
    Navegação entre meses. Mantém os outros filtros da URL.

    Props: $filter (MonthFilter), $route (nome da rota, ex.: 'dashboard')
    e $params (filtros atuais da URL, que não podem se perder ao trocar de mês).
    array_merge junta os filtros com o novo ?mes=. O link "Hoje" usa
    array_diff_key para tirar o ?mes= e cair no mês atual.
--}}
<div class="flex items-center gap-1">
    <a href="{{ route($route, array_merge($params, ['mes' => $filter->previous()])) }}"
       class="btn-ghost px-2.5" aria-label="Mês anterior">←</a>

    <span class="min-w-40 text-center font-display text-[18px] first-letter:uppercase">{{ $filter->label() }}</span>

    <a href="{{ route($route, array_merge($params, ['mes' => $filter->next()])) }}"
       class="btn-ghost px-2.5" aria-label="Próximo mês">→</a>

    @unless ($filter->isCurrent())
        <a href="{{ route($route, array_diff_key($params, ['mes' => 1])) }}" class="ml-1 text-[13px] text-ink-soft underline decoration-highlight decoration-2 underline-offset-4 hover:text-ink">Hoje</a>
    @endunless
</div>
