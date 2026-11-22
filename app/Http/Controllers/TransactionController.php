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

/**
 * CRUD de lançamentos, com filtros, totais e exportação em CSV.
 */
class TransactionController extends Controller
{
    /**
     * GET /lancamentos: lista do mês com filtros por tipo, categoria e busca.
     * Ex.: /lancamentos?mes=2026-11&tipo=saida&busca=mercado
     */
    public function index(Request $request): View
    {
        $filter = MonthFilter::fromRequest($request);
        $query = $this->filteredQuery($request, $filter);

        // Totais do filtro inteiro, não só da página atual.
        // clone porque a consulta é um objeto: sem ele, o get() e o
        // paginate() abaixo mexeriam na mesma consulta.
        $all = (clone $query)->with('category')->get();
        $income = (float) $all->filter(fn ($t) => $t->type() === TransactionType::Income)->sum('amount');
        $expense = (float) $all->filter(fn ($t) => $t->type() === TransactionType::Expense)->sum('amount');

        return view('transactions.index', [
            'transactions' => $query->with('category')
                ->orderByDesc('occurred_on')->orderByDesc('id')
                // 20 por página. withQueryString() mantém os filtros (?mes, ?busca...)
                // nos links de página 2, 3...
                ->paginate(20)->withQueryString(),
            'filter' => $filter,
            'income' => $income,
            'expense' => $expense,
            'categories' => $request->user()->categories()->orderBy('name')->get(),
        ]);
    }

    /**
     * GET /lancamentos/exportar: baixa um CSV com os mesmos filtros da lista.
     */
    public function export(Request $request): StreamedResponse
    {
        $filter = MonthFilter::fromRequest($request);

        $transactions = $this->filteredQuery($request, $filter)
            ->with('category')
            ->orderBy('occurred_on')->orderBy('id')
            ->get();

        $fileName = "lancamentos-{$filter->key()}.csv";

        // streamDownload escreve o arquivo direto na resposta, sem salvar
        // em disco. O navegador recebe como download.
        return response()->streamDownload(function () use ($transactions) {
            // php://output é a própria saída da resposta HTTP.
            $out = fopen('php://output', 'w');

            // BOM e ponto e vírgula para o Excel em português abrir sem bagunçar acentos e colunas.
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Data', 'Descrição', 'Categoria', 'Tipo', 'Valor', 'Observação'], ';', escape: '');

            foreach ($transactions as $t) {
                $type = $t->type();
                // No CSV a saída vai negativa, para dar para somar a coluna no Excel.
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

    /**
     * GET /lancamentos/novo: formulário vazio. ?tipo=entrada abre com a aba Entrada marcada.
     */
    public function create(Request $request): View
    {
        // tryFrom devolve null para valor inválido, e o ?? cai em Saída.
        $type = TransactionType::tryFrom((string) $request->query('tipo')) ?? TransactionType::Expense;

        return view('transactions.form', [
            // Lançamento novo com a data de hoje já preenchida.
            'transaction' => new Transaction(['occurred_on' => now()]),
            'type' => $type,
            'categories' => $this->categoriesFor($request),
        ]);
    }

    /**
     * POST /lancamentos: salva e volta para a lista no mês do lançamento.
     */
    public function store(TransactionRequest $request): RedirectResponse
    {
        $transaction = $request->user()->transactions()->create($request->validated());

        return redirect()
            ->route('transactions.index', ['mes' => $transaction->occurred_on->format('Y-m')])
            ->with('status', 'Lançamento registrado.');
    }

    /**
     * GET /lancamentos/{transaction}/editar. O {transaction} da URL vira
     * o objeto pelo route model binding.
     */
    public function edit(Request $request, Transaction $transaction): View
    {
        // TransactionPolicy::update: 403 se o lançamento for de outra pessoa.
        Gate::authorize('update', $transaction);

        return view('transactions.form', [
            'transaction' => $transaction,
            'type' => $transaction->type(),
            'categories' => $this->categoriesFor($request),
        ]);
    }

    /**
     * PUT /lancamentos/{transaction}: a checagem de dono está no
     * authorize() do TransactionRequest.
     */
    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $transaction->update($request->validated());

        return redirect()
            ->route('transactions.index', ['mes' => $transaction->occurred_on->format('Y-m')])
            ->with('status', 'Lançamento atualizado.');
    }

    /**
     * DELETE /lancamentos/{transaction}.
     */
    public function destroy(Transaction $transaction): RedirectResponse
    {
        Gate::authorize('delete', $transaction);

        $transaction->delete();

        // back() volta para a página de onde a pessoa veio, mantendo os filtros.
        return back()->with('status', 'Lançamento apagado.');
    }

    /**
     * Lançamentos do usuário no mês, com os filtros opcionais de tipo, categoria e busca.
     * Devolve a consulta ainda sem executar, para index() e export() completarem.
     */
    private function filteredQuery(Request $request, MonthFilter $filter)
    {
        // Partir de $request->user()->transactions() já limita aos dados do usuário logado.
        $query = $request->user()->transactions()->inMonth($filter->month);

        if ($type = TransactionType::tryFrom((string) $request->query('tipo'))) {
            $query->ofType($type);
        }

        // integer() devolve 0 quando o parâmetro não veio, e 0 é falso no if.
        if ($categoryId = $request->integer('categoria')) {
            $query->where('category_id', $categoryId);
        }

        if ($search = trim((string) $request->query('busca'))) {
            // Os dois where ficam dentro de um grupo (parênteses no SQL) para o
            // OR não escapar do filtro de usuário e mês:
            // ... AND (description LIKE ? OR notes LIKE ?)
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
