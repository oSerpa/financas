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

#[Fillable(['category_id', 'description', 'amount', 'occurred_on', 'notes'])]
class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'occurred_on' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** O tipo do lançamento é sempre o tipo da categoria. */
    public function type(): TransactionType
    {
        return $this->category->type;
    }

    public function scopeInMonth(Builder $query, Carbon $month): void
    {
        $query->whereBetween('occurred_on', [
            $month->copy()->startOfMonth()->toDateString(),
            $month->copy()->endOfMonth()->toDateString(),
        ]);
    }

    public function scopeOfType(Builder $query, TransactionType $type): void
    {
        $query->whereHas('category', fn (Builder $q) => $q->where('type', $type));
    }
}
