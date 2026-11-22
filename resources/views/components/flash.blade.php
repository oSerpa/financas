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
