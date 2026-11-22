@php
    $mes = now()->locale('pt_BR')->translatedFormat('F \d\e Y');
    $primeiroNome = strtok(auth()->user()->name, ' ');
@endphp

<x-layouts.app title="Painel">
    <div class="flex flex-wrap items-end justify-between gap-4 border-b border-ink pb-6">
        <div>
            <p class="num text-[12px] tracking-wider text-muted uppercase">{{ $mes }}</p>
            <h1 class="mt-1 font-display text-[38px] leading-tight">Olá, {{ $primeiroNome }}.</h1>
        </div>
        <span class="btn-primary cursor-default opacity-50" title="Na próxima etapa">+ Novo lançamento</span>
    </div>

    {{-- Resumo do mês: valores zerados até existirem lançamentos (etapa 2). --}}
    <section class="mt-8 grid gap-px overflow-hidden rounded-[3px] border border-rule bg-rule sm:grid-cols-3">
        <div class="bg-card p-6">
            <p class="text-[13px] text-muted">Entradas</p>
            <p class="num mt-2 text-[28px] text-income">R$ 0,00</p>
        </div>
        <div class="bg-card p-6">
            <p class="text-[13px] text-muted">Saídas</p>
            <p class="num mt-2 text-[28px] text-expense">R$ 0,00</p>
        </div>
        <div class="bg-card p-6">
            <p class="text-[13px] text-muted">Saldo</p>
            <p class="num mt-2 text-[28px]">R$ 0,00</p>
        </div>
    </section>

    <section class="ruled mt-10 rounded-[3px] border border-rule bg-card px-6 py-14 text-center">
        <div class="mx-auto max-w-md bg-card px-6 py-5">
            <p class="font-display text-[26px] italic">Página em branco, por enquanto.</p>
            <p class="mt-3 text-[15px] text-ink-soft">
                Quando você registrar suas primeiras entradas e saídas, o resumo do mês e os gráficos aparecem aqui.
            </p>
        </div>
    </section>
</x-layouts.app>
