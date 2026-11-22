{{--
    Tela de cadastro (GET /cadastro), enviada para RegisterController@store.
    O campo password_confirmation é exigido pela regra 'confirmed' da senha.
--}}
<x-layouts.guest title="Criar conta">
    <h1 class="font-display text-[34px] leading-tight">Abra sua caderneta.</h1>
    <p class="mt-2 text-[15px] text-muted">Leva menos de um minuto.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-9 space-y-5">
        @csrf

        <div>
            <label for="name" class="field-label">Nome</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}"
                   class="field-input" autocomplete="name" required autofocus>
            @error('name') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="field-label">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}"
                   class="field-input" autocomplete="email" required>
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="field-label">Senha</label>
            <input id="password" name="password" type="password"
                   class="field-input" autocomplete="new-password" required>
            <p class="mt-1.5 text-[12px] text-muted">Mínimo de 8 caracteres.</p>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Confirmar senha</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   class="field-input" autocomplete="new-password" required>
        </div>

        <button type="submit" class="btn-primary w-full">Criar conta</button>
    </form>

    <p class="mt-8 border-t border-rule pt-6 text-[14px] text-muted">
        Já tem conta?
        <a href="{{ route('login') }}" class="font-medium text-ink underline decoration-highlight decoration-2 underline-offset-4">Entrar</a>
    </p>
</x-layouts.guest>
