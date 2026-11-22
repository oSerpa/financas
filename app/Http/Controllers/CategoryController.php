<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * CRUD de categorias. Cada método público corresponde a uma rota do
 * Route::resource('categorias', ...) em routes/web.php.
 */
class CategoryController extends Controller
{
    /**
     * GET /categorias: lista as categorias separadas em Entradas e Saídas.
     */
    public function index(Request $request): View
    {
        $categories = $request->user()->categories()
            // withCount cria o atributo transactions_count, mostrado na tela,
            // sem precisar carregar todos os lançamentos.
            ->withCount('transactions')
            ->orderBy('name')
            ->get()
            // Agrupa a coleção pelo tipo: ['entrada' => [...], 'saida' => [...]].
            ->groupBy(fn (Category $c) => $c->type->value);

        // Monta um grupo para cada caso do enum, mesmo que esteja vazio,
        // para a tela sempre mostrar as duas colunas.

        return view('categories.index', [
            'groups' => collect(TransactionType::cases())
                ->mapWithKeys(fn ($type) => [$type->value => [
                    'type' => $type,
                    'items' => $categories->get($type->value, collect()),
                ]]),
        ]);
    }

    /**
     * GET /categorias/novo: formulário vazio. ?tipo=entrada já deixa o
     * tipo marcado (vem do botão "Nova" de cada coluna).
     */
    public function create(Request $request): View
    {
        // O mesmo form.blade.php serve para criar e editar. Aqui ele recebe
        // uma Category nova, ainda não salva no banco.
        return view('categories.form', [
            'category' => new Category(['type' => $request->query('tipo', TransactionType::Expense->value)]),
        ]);
    }

    /**
     * POST /categorias: salva a nova categoria.
     *
     * Receber CategoryRequest (e não Request) faz o Laravel validar antes
     * de entrar no método. Se falhar, volta ao formulário com os erros.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        // validated() devolve só os campos que passaram pelas regras.
        // Criar pela relação preenche o user_id com o usuário logado.
        $request->user()->categories()->create($request->validated());

        // with() grava uma mensagem "flash" na sessão, que aparece uma vez
        // na próxima página (componente flash).
        return redirect()->route('categories.index')->with('status', 'Categoria criada.');
    }

    /**
     * GET /categorias/{category}/editar: formulário preenchido.
     *
     * Route model binding: o Laravel lê o {category} da URL, busca a
     * Category com esse id e entrega pronta. Se não existir, responde 404.
     */
    public function edit(Category $category): View
    {
        // Chama CategoryPolicy::update. Se a categoria for de outra pessoa,
        // responde 403 e o método para aqui.
        Gate::authorize('update', $category);

        return view('categories.form', ['category' => $category]);
    }

    /**
     * PUT /categorias/{category}: salva a edição. A checagem de dono fica
     * no authorize() do CategoryRequest.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('status', 'Categoria atualizada.');
    }

    /**
     * DELETE /categorias/{category}: apaga a categoria, se estiver vazia.
     */
    public function destroy(Category $category): RedirectResponse
    {
        Gate::authorize('delete', $category);

        // O banco também bloqueia (restrictOnDelete na migration), mas
        // checar antes permite mostrar uma mensagem amigável.
        if ($category->transactions()->exists()) {
            return back()->with('error', 'Essa categoria tem lançamentos. Mova ou apague os lançamentos antes.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('status', 'Categoria apagada.');
    }
}
