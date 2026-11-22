<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $month = now();

        $income = (float) $user->transactions()->inMonth($month)->ofType(TransactionType::Income)->sum('amount');
        $expense = (float) $user->transactions()->inMonth($month)->ofType(TransactionType::Expense)->sum('amount');

        return view('dashboard', [
            'month' => $month,
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'latest' => $user->transactions()->with('category')
                ->orderByDesc('occurred_on')->orderByDesc('id')->limit(6)->get(),
        ]);
    }
}
