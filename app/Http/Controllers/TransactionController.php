<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = $request->user()->transactions()
            ->with('category')
            ->orderByDesc('occurred_on')
            ->orderByDesc('id')
            ->paginate(20);

        return view('transactions.index', ['transactions' => $transactions]);
    }

    public function create(Request $request): View
    {
        $type = TransactionType::tryFrom((string) $request->query('tipo')) ?? TransactionType::Expense;

        return view('transactions.form', [
            'transaction' => new Transaction(['occurred_on' => now()]),
            'type' => $type,
            'categories' => $this->categoriesFor($request),
        ]);
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $request->user()->transactions()->create($request->validated());

        return redirect()->route('transactions.index')->with('status', 'Lançamento registrado.');
    }

    public function edit(Request $request, Transaction $transaction): View
    {
        Gate::authorize('update', $transaction);

        return view('transactions.form', [
            'transaction' => $transaction,
            'type' => $transaction->type(),
            'categories' => $this->categoriesFor($request),
        ]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update($request->validated());

        return redirect()->route('transactions.index')->with('status', 'Lançamento atualizado.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);

        $transaction->delete();

        return back()->with('status', 'Lançamento apagado.');
    }

    /** Categorias do usuário agrupadas por tipo, para o select do formulário. */
    private function categoriesFor(Request $request)
    {
        return $request->user()->categories()->orderBy('name')->get()
            ->groupBy(fn ($c) => $c->type->value);
    }
}
