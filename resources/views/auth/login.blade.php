<x-layouts.guest title="Entrar">
    <h1 class="font-display text-[34px] leading-tight">Bom te ver de novo.</h1>
    <p class="mt-2 text-[15px] text-muted">Entre para continuar suas anotações.</p>

    <form method="POST" action="{{ route('login') }}" class="mt-9 space-y-5">
        @csrf

        <div>
            <label for="email" class="field-label">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   class="field-input" autocomplete="email" required autofocus>
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="field-label">Senha</label>
            <input id="password" name="password" type="password"
                   class="field-input" autocomplete="current-password" required>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <label class="flex items-center gap-2 text-[14px] text-ink-soft">
            <input type="checkbox" name="remember" class="size-4 rounded-[2px] border-rule accent-ink">
            Manter conectado
        </label>

        <button type="submit" class="btn-primary w-full">Entrar</button>
    </form>

    @if (config('app.registration_enabled'))
        <p class="mt-8 border-t border-rule pt-6 text-[14px] text-muted">
            Ainda não tem conta?
            <a href="{{ route('register') }}" class="font-medium text-ink underline decoration-highlight decoration-2 underline-offset-4">Criar uma agora</a>
        </p>
    @endif
</x-layouts.guest>
