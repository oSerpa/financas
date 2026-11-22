<x-layouts.guest title="Esqueci minha senha">
    <h1 class="font-display text-[34px] leading-tight">Esqueceu a senha?</h1>
    <p class="mt-2 text-[15px] text-muted">Informe o e-mail da sua conta e enviaremos um link para criar uma nova.</p>

    <div class="mt-9">
        <x-flash />
    </div>

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="field-label">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   class="field-input" autocomplete="email" required autofocus>
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="btn-primary w-full">Enviar link</button>
    </form>

    <p class="mt-8 border-t border-rule pt-6 text-[14px] text-muted">
        Lembrou?
        <a href="{{ route('login') }}" class="font-medium text-ink underline decoration-highlight decoration-2 underline-offset-4">Voltar para o login</a>
    </p>
</x-layouts.guest>
