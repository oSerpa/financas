<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = $request->user()->categories()
            ->withCount('transactions')
            ->orderBy('name')
            ->get()
            ->groupBy(fn (Category $c) => $c->type->value);

        return view('categories.index', [
            'groups' => collect(TransactionType::cases())
                ->mapWithKeys(fn ($type) => [$type->value => [
                    'type' => $type,
                    'items' => $categories->get($type->value, collect()),
                ]]),
        ]);
    }

    public function create(Request $request): View
    {
        return view('categories.form', [
            'category' => new Category(['type' => $request->query('tipo', TransactionType::Expense->value)]),
        ]);
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $request->user()->categories()->create($request->validated());

        return redirect()->route('categories.index')->with('status', 'Categoria criada.');
    }

    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        return view('categories.form', ['category' => $category]);
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('status', 'Categoria atualizada.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        if ($category->transactions()->exists()) {
            return back()->with('error', 'Essa categoria tem lançamentos. Mova ou apague os lançamentos antes.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Categoria apagada.');
    }
}
