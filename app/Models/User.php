<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\TransactionType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /** Categorias iniciais para a conta não começar vazia. */
    public function createDefaultCategories(): void
    {
        $defaults = [
            TransactionType::Income->value => ['Salário', 'Freelance', 'Outras entradas'],
            TransactionType::Expense->value => ['Moradia', 'Mercado', 'Transporte', 'Lazer', 'Saúde', 'Contas'],
        ];

        foreach ($defaults as $type => $names) {
            foreach ($names as $name) {
                $this->categories()->create(['name' => $name, 'type' => $type]);
            }
        }
    }
}
