<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Models\Transaction;
use App\Support\MonthFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $filter = MonthFilter::fromRequest($request);

        $monthTransactions = $user->transactions()->with('category')->inMonth($filter->month)->get();

        $income = $this->sumOf($monthTransactions, TransactionType::Income);
        $expense = $this->sumOf($monthTransactions, TransactionType::Expense);

        return view('dashboard', [
            'filter' => $filter,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'byCategory' => $this->expensesByCategory($monthTransactions, $expense),
            'history' => $this->lastSixMonths($request, $filter),
            'latest' => $monthTransactions->sortByDesc(fn ($t) => [$t->occurred_on, $t->id])->take(6),
        ]);
    }

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
            ->map(function (Collection $items) use ($totalExpense) {
                $total = (float) $items->sum('amount');

                return [
                    'name' => $items->first()->category->name,
                    'total' => $total,
                    'percent' => $totalExpense > 0 ? round($total / $totalExpense * 100, 1) : 0,
                ];
            })
            ->sortByDesc('total')
            ->values();
    }

    /** Entradas e saídas dos 6 meses que terminam no mês filtrado. */
    private function lastSixMonths(Request $request, MonthFilter $filter): Collection
    {
        $start = $filter->month->copy()->subMonths(5)->startOfMonth();
        $end = $filter->month->copy()->endOfMonth();

        $rows = $request->user()->transactions()
            ->with('category')
            ->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(fn (Transaction $t) => $t->occurred_on->format('Y-m'));

        return collect(range(0, 5))->map(function (int $i) use ($start, $rows) {
            $month = $start->copy()->addMonths($i);
            $items = $rows->get($month->format('Y-m'), collect());

            return [
                'key' => $month->format('Y-m'),
                'label' => $month->locale('pt_BR')->translatedFormat('M'),
                'full' => $month->locale('pt_BR')->translatedFormat('F \d\e Y'),
                'income' => $this->sumOf($items, TransactionType::Income),
                'expense' => $this->sumOf($items, TransactionType::Expense),
            ];
        });
    }
}
