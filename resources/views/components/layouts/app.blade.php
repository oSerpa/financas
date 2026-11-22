{{--
    Layout das páginas internas (usuário logado). Uma página usa assim:
        <x-layouts.app title="Categorias"> conteúdo </x-layouts.app>
    O conteúdo entra no {{ $slot }} e o atributo title vira a variável $title.
--}}
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Painel' }} · {{ config('app.name') }}</title>
    {{-- @vite inclui o CSS e o JS compilados. Em desenvolvimento aponta para o npm run dev. --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <header class="border-b border-rule bg-paper">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-6 px-5 py-4 sm:px-8">
            <div class="flex items-center gap-10">
                <x-logo />
                {{-- request()->routeIs() marca o item do menu da página atual. 'transactions.*' casa com todas as rotas de lançamentos. --}}
                <nav class="hidden gap-1 sm:flex">
                    <a href="{{ route('dashboard') }}"
                       @class(['btn-ghost', 'bg-paper-dark text-ink' => request()->routeIs('dashboard')])>Painel</a>
                    <a href="{{ route('transactions.index') }}"
                       @class(['btn-ghost', 'bg-paper-dark text-ink' => request()->routeIs('transactions.*')])>Lançamentos</a>
                    <a href="{{ route('categories.index') }}"
                       @class(['btn-ghost', 'bg-paper-dark text-ink' => request()->routeIs('categories.*')])>Categorias</a>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <span class="hidden text-[14px] text-ink-soft md:inline">{{ auth()->user()->name }}</span>
                {{-- Sair é um formulário POST com @csrf, não um link. --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-ghost">Sair</button>
                </form>
            </div>
        </div>
    </header>

    {{-- Navegação no celular --}}
    <nav class="flex gap-1 overflow-x-auto border-b border-rule px-3 py-2 sm:hidden">
        <a href="{{ route('dashboard') }}" @class(['btn-ghost', 'bg-paper-dark text-ink' => request()->routeIs('dashboard')])>Painel</a>
        <a href="{{ route('transactions.index') }}" @class(['btn-ghost', 'bg-paper-dark text-ink' => request()->routeIs('transactions.*')])>Lançamentos</a>
        <a href="{{ route('categories.index') }}" @class(['btn-ghost', 'bg-paper-dark text-ink' => request()->routeIs('categories.*')])>Categorias</a>
    </nav>

    <main class="mx-auto max-w-6xl px-5 py-10 sm:px-8">
        <x-flash />
        {{-- Aqui entra o conteúdo de cada página. --}}
        {{ $slot }}
    </main>
</body>
</html>
