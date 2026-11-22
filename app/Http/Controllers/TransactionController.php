<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\TransactionRequest;
use App\Models\Transaction;
use App\Support\MonthFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $filter = MonthFilter::fromRequest($request);
        $query = $this->filteredQuery($request, $filter);

        // Totais do filtro inteiro, não só da página atual.
        $all = (clone $query)->with('category')->get();
        $income = (float) $all->filter(fn ($t) => $t->type() === TransactionType::Income)->sum('amount');
        $expense = (float) $all->filter(fn ($t) => $t->type() === TransactionType::Expense)->sum('amount');

        return view('transactions.index', [
            'transactions' => $query->with('category')
                ->orderByDesc('occurred_on')->orderByDesc('id')
                ->paginate(20)->withQueryString(),
            'filter' => $filter,
            'income' => $income,
            'expense' => $expense,
            'categories' => $request->user()->categories()->orderBy('name')->get(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filter = MonthFilter::fromRequest($request);

        $transactions = $this->filteredQuery($request, $filter)
            ->with('category')
            ->orderBy('occurred_on')->orderBy('id')
            ->get();

        $fileName = "lancamentos-{$filter->key()}.csv";

        return response()->streamDownload(function () use ($transactions) {
            $out = fopen('php://output', 'w');

            // BOM e ponto e vírgula para o Excel em português abrir sem bagunçar acentos e colunas.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Data', 'Descrição', 'Categoria', 'Tipo', 'Valor', 'Observação'], ';', escape: '');

            foreach ($transactions as $t) {
                $type = $t->type();
                $signed = $type === TransactionType::Expense ? -$t->amount : $t->amount;

                fputcsv($out, [
                    $t->occurred_on->format('d/m/Y'),
                    $t->description,
                    $t->category->name,
                    $type->label(),
                    number_format((float) $signed, 2, ',', ''),
                    $t->notes,
                ], ';', escape: '');
            }

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
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
        $transaction = $request->user()->transactions()->create($request->validated());

        return redirect()
            ->route('transactions.index', ['mes' => $transaction->occurred_on->format('Y-m')])
            ->with('status', 'Lançamento registrado.');
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

        return redirect()
            ->route('transactions.index', ['mes' => $transaction->occurred_on->format('Y-m')])
            ->with('status', 'Lançamento atualizado.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);

        $transaction->delete();

        return back()->with('status', 'Lançamento apagado.');
    }

    /** Lançamentos do usuário no mês, com os filtros opcionais de tipo, categoria e busca. */
    private function filteredQuery(Request $request, MonthFilter $filter)
    {
        $query = $request->user()->transactions()->inMonth($filter->month);

        if ($type = TransactionType::tryFrom((string) $request->query('tipo'))) {
            $query->ofType($type);
        }

        if ($categoryId = $request->integer('categoria')) {
            $query->where('category_id', $categoryId);
        }

        if ($search = trim((string) $request->query('busca'))) {
            $query->where(fn (Builder $q) => $q
                ->where('description', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%"));
        }

        return $query;
    }

    /** Categorias do usuário agrupadas por tipo, para o select do formulário. */
    private function categoriesFor(Request $request)
    {
        return $request->user()->categories()->orderBy('name')->get()
            ->groupBy(fn ($c) => $c->type->value);
    }
}
