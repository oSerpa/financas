<?php

namespace App\Models;

use App\Enums\TransactionType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Categoria de um lançamento (Mercado, Salário...). Representa uma linha
 * da tabela "categories".
 *
 * Cada categoria pertence a um usuário e tem um tipo (entrada ou saída).
 * É o tipo da categoria que define se o lançamento soma ou subtrai.
 */
// #[Fillable] lista os campos que podem ser preenchidos em massa com
// create() ou update(). user_id fica de fora de propósito: ele é definido
// pela relação ($user->categories()->create(...)), nunca pelo formulário.
#[Fillable(['name', 'type'])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory; // permite Category::factory() nos testes e seeders

    /**
     * Casts convertem o valor do banco num tipo PHP. Aqui a coluna "type"
     * (texto 'entrada'/'saida') vira o enum TransactionType automaticamente.
     */
    protected function casts(): array
    {
        return [
            'type' => TransactionType::class,
        ];
    }

    /** A categoria pertence a um usuário (coluna user_id). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Uma categoria tem vários lançamentos (transactions.category_id). */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
