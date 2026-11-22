<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Entrar' }} · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">
    <div class="grid min-h-screen lg:grid-cols-[1.05fr_1fr]">
        {{-- Lado esquerdo: página de caderneta ilustrativa --}}
        <aside class="relative hidden overflow-hidden bg-ink p-12 text-paper lg:flex lg:flex-col lg:justify-between">
            <x-logo dark />

            <div class="max-w-md">
                <p class="font-display text-[44px] leading-[1.05]">
                    Cada real anotado<br>
                    <span class="italic text-highlight">é um real que você entende.</span>
                </p>

                <div class="num mt-12 space-y-0 text-[13px] text-paper/70">
                    <div class="flex justify-between border-b border-paper/15 py-2.5">
                        <span>03/11 &nbsp;Salário</span><span class="text-[#9fc7a6]">+ 4.200,00</span>
                    </div>
                    <div class="flex justify-between border-b border-paper/15 py-2.5">
                        <span>05/11 &nbsp;Aluguel</span><span class="text-[#e29a84]">− 1.350,00</span>
                    </div>
                    <div class="flex justify-between border-b border-paper/15 py-2.5">
                        <span>09/11 &nbsp;Mercado</span><span class="text-[#e29a84]">− 486,30</span>
                    </div>
                    <div class="flex justify-between py-2.5 text-paper">
                        <span>saldo do mês</span><span class="border-b-2 border-highlight">2.363,70</span>
                    </div>
                </div>
                <p class="mt-3 text-[12px] text-paper/40">Exemplo ilustrativo.</p>
            </div>

            <p class="text-[12px] text-paper/40">Projeto de portfólio · Laravel + MySQL</p>
        </aside>

        {{-- Lado direito: formulário --}}
        <main class="flex items-center justify-center px-6 py-12 sm:px-12">
            <div class="w-full max-w-sm">
                <div class="mb-10 lg:hidden"><x-logo /></div>
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
