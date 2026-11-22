{{--
    Botão de apagar: <x-delete-button :action="route('...destroy', $item)" />.

    HTML só envia GET e POST. O @method('DELETE') cria um campo escondido
    _method=DELETE, e o Laravel trata o POST como DELETE para casar com a rota
    destroy. O @csrf adiciona o token que protege contra envio de outro site.
    O onsubmit pede confirmação no navegador antes de enviar. @js() converte
    o texto em string JavaScript com segurança (escapando aspas).
--}}
@props(['action', 'confirm' => 'Apagar mesmo? Não dá para desfazer.'])

<form method="POST" action="{{ $action }}" onsubmit="return confirm(@js($confirm))" class="flex">
    @csrf
    @method('DELETE')
    <button type="submit" class="leading-5 text-[13px] text-muted underline-offset-4 transition hover:text-expense hover:underline">Apagar</button>
</form>
