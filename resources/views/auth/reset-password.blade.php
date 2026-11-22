<x-layouts.guest title="Nova senha">
    <h1 class="font-display text-[34px] leading-tight">Crie uma nova senha.</h1>
    <p class="mt-2 text-[15px] text-muted">Depois disso você já pode entrar com ela.</p>

    <form method="POST" action="{{ route('password.update') }}" class="mt-9 space-y-5">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="field-label">E-mail</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}"
                   class="field-input" autocomplete="email" required>
            @error('email') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="field-label">Nova senha</label>
            <input id="password" name="password" type="password"
                   class="field-input" autocomplete="new-password" required autofocus>
            <p class="mt-1.5 text-[12px] text-muted">Mínimo de 8 caracteres.</p>
            @error('password') <p class="field-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="field-label">Confirmar nova senha</label>
            <input id="password_confirmation" name="password_confirmation" type="password"
                   class="field-input" autocomplete="new-password" required>
        </div>

        <button type="submit" class="btn-primary w-full">Salvar nova senha</button>
    </form>
</x-layouts.guest>
