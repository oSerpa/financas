<?php

namespace App\Models;

use App\Enums\TransactionType;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Lançamento financeiro (uma entrada ou uma saída de dinheiro).
 * Representa uma linha da tabela "transactions".
 *
 * O valor (amount) é sempre positivo. Se ele soma ou subtrai depende
 * do tipo da categoria, veja o método type().
 */
// user_id não está aqui pelo mesmo motivo da Category: vem da relação.
#[Fillable(['category_id', 'description', 'amount', 'occurred_on', 'notes'])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            // decimal:2 devolve o valor como string com 2 casas ("1234.50"),
            // evitando erros de arredondamento de float com dinheiro.
            'amount' => 'decimal:2',
            // date transforma a coluna num objeto Carbon, então dá para
            // usar $transaction->occurred_on->format('d/m/Y') nas views.
            'occurred_on' => 'date',
        ];
    }

    /** O lançamento pertence a um usuário. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** O lançamento pertence a uma categoria. */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** O tipo do lançamento é sempre o tipo da categoria. */
    public function type(): TransactionType
    {
        return $this->category->type;
    }

    /**
     * Local scope: métodos que começam com "scope" viram filtros
     * encadeáveis na consulta. Este é usado como ->inMonth($mes) e
     * traz só os lançamentos entre o primeiro e o último dia do mês.
     */
    public function scopeInMonth(Builder $query, Carbon $month): void
    {
        $query->whereBetween('occurred_on', [
            // copy() evita alterar a data original, porque Carbon é mutável.
            $month->copy()->startOfMonth()->toDateString(),
            $month->copy()->endOfMonth()->toDateString(),
        ]);
    }

    /**
     * Usado como ->ofType(TransactionType::Income). Como o tipo fica na
     * categoria, o whereHas filtra pelos lançamentos cuja categoria tem
     * aquele tipo (gera um EXISTS com subconsulta no SQL).
     */
    public function scopeOfType(Builder $query, TransactionType $type): void
    {
        $query->whereHas('category', fn (Builder $q) => $q->where('type', $type));
    }
}
