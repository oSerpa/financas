{{--
    Mensagens "flash": gravadas pelo controller com ->with('status', '...')
    e lidas aqui com session(). Aparecem só na página seguinte e somem.
    'status' é a mensagem de sucesso (verde) e 'error' a de erro (vermelho).
--}}
@if (session('status'))
    <div class="mb-6 flex items-center gap-3 border-l-[3px] border-income bg-income-soft px-4 py-3 text-[14px] text-income">
        {{ session('status') }}
    </div>
@endif
@if (session('error'))
    <div class="mb-6 flex items-center gap-3 border-l-[3px] border-expense bg-expense-soft px-4 py-3 text-[14px] text-expense">
        {{ session('error') }}
    </div>
@endif
