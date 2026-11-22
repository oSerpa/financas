<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Support\MonthFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Painel inicial: totais do mês, gráfico por categoria, histórico de
 * 6 meses e últimos lançamentos.
 *
 * É um "single action controller": tem só o __invoke, chamado quando a
 * rota aponta para a classe inteira (Route::get('/painel', DashboardController::class)).
 */
class DashboardController extends Controller
{
    /**
     * GET /painel (aceita ?mes=AAAA-MM).
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $filter = MonthFilter::fromRequest($request);

        // with('category') é eager loading: busca as categorias numa consulta
        // só, em vez de uma por lançamento (o problema N+1).
        // inMonth() é o scope definido no model Transaction.
        $monthTransactions = $user->transactions()->with('category')->inMonth($filter->month)->get();

        // As somas são feitas na coleção já carregada, sem nova ida ao banco.

        $income = $this->sumOf($monthTransactions, TransactionType::Income);
        $expense = $this->sumOf($monthTransactions, TransactionType::Expense);

        return view('dashboard', [
            'filter' => $filter,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'byCategory' => $this->expensesByCategory($monthTransactions, $expense),
            'history' => $this->lastSixMonths($request, $filter),
            // Ordena por data e, no empate, pelo id: o mais recente primeiro.
            'latest' => $monthTransactions->sortByDesc(fn ($t) => [$t->occurred_on, $t->id])->take(6),
        ]);
    }

    /** Soma os valores dos lançamentos de um tipo (entrada ou saída). */
    private function sumOf(Collection $transactions, TransactionType $type): float
    {
        return (float) $transactions->filter(fn ($t) => $t->type() === $type)->sum('amount');
    }

    /** Saídas do mês somadas por categoria, da maior para a menor. */
    private function expensesByCategory(Collection $transactions, float $totalExpense): Collection
    {
        return $transactions
            ->filter(fn ($t) => $t->type() === TransactionType::Expense)
            ->groupBy('category_id')
            // use ($totalExpense) traz a variável de fora para dentro da função anônima.
            ->map(function (Collection $items) use ($totalExpense) {
                $total = (float) $items->sum('amount');

                return [
                    'name' => $items->first()->category->name,
                    'total' => $total,
                    // Evita divisão por zero num mês sem saídas.
                    'percent' => $totalExpense > 0 ? round($total / $totalExpense * 100, 1) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values(); // reindexa as chaves para 0, 1, 2...
    }

    /** Entradas e saídas dos 6 meses que terminam no mês filtrado. */
    private function lastSixMonths(Request $request, MonthFilter $filter): Collection
    {
        // Ex.: filtro em novembro gera de junho a novembro.
        $start = $filter->month->copy()->subMonths(5)->startOfMonth();
        $end = $filter->month->copy()->endOfMonth();

        $rows = $request->user()->transactions()
            ->with('category')
            ->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn (Transaction $t) => $t->occurred_on->format('Y-m'));

        // range(0, 5) garante os 6 meses no gráfico, inclusive os sem lançamento.
        return collect(range(0, 5))->map(function (int $i) use ($start, $rows) {
            $month = $start->copy()->addMonths($i);
            $items = $rows->get($month->format('Y-m'), collect());

            return [
                'key' => $month->format('Y-m'),
                'label' => $month->locale('pt_BR')->translatedFormat('M'), // "nov", embaixo da barra
                'full' => $month->locale('pt_BR')->translatedFormat('F \d\e Y'), // "novembro de 2026", no texto de ajuda
                'income' => $this->sumOf($items, TransactionType::Income),
                'expense' => $this->sumOf($items, TransactionType::Expense),
            ];
        });
    }
}
