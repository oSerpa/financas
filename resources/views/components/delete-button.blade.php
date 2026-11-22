@props(['action', 'confirm' => 'Apagar mesmo? Não dá para desfazer.'])

<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($confirm))" class="flex">
    @csrf
    @method('DELETE')
    <button type="submit" class="leading-5 text-[13px] text-muted underline-offset-4 transition hover:text-expense hover:underline">Apagar</button>
</form>
