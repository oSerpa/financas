{{--
    Logo da Caderneta com link para o painel. <x-logo dark /> usa as cores
    para fundo escuro (lado esquerdo da tela de login).
--}}
@props(['dark' => false])

<a href="{{ route('dashboard') }}" {{ $attributes->merge(['class' => 'inline-flex items-baseline gap-2']) }}>
    <span class="font-display text-[26px] leading-none italic {{ $dark ? 'text-paper' : 'text-ink' }}">Caderneta</span>
    <span class="num text-[11px] tracking-wider uppercase {{ $dark ? 'text-paper/50' : 'text-muted' }}">finanças</span>
</a>
