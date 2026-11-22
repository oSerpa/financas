<?php

namespace App\Models;

use App\Enums\TransactionType;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuário do sistema. Estende Authenticatable (e não Model direto) para
 * funcionar com o login do Laravel: Auth::attempt(), auth()->user() etc.
 */
#[Fillable(['name', 'email', 'password'])]
// #[Hidden] esconde estes campos quando o model vira array ou JSON.
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    // Notifiable permite $user->notify(...), usado no e-mail de redefinir senha.
    use HasFactory, Notifiable;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            // 'hashed' aplica o bcrypt sozinho ao salvar. Por isso o
            // controller pode passar a senha pura em User::create().
            'password' => 'hashed',
        ];
    }

    /** Categorias criadas por este usuário. */
    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    /**
     * Lançamentos deste usuário. Toda consulta parte daqui
     * ($request->user()->transactions()), o que garante que ninguém vê
     * dados de outra pessoa.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /** Categorias iniciais para a conta não começar vazia. Chamado no cadastro. */
    public function createDefaultCategories(): void
    {
        // Chave = tipo ('entrada'/'saida'), valor = nomes das categorias.
        $defaults = [
            TransactionType::Income->value => ['Salário', 'Freelance', 'Outras entradas'],
            TransactionType::Expense->value => ['Moradia', 'Mercado', 'Transporte', 'Lazer', 'Saúde', 'Contas'],
        ];

        foreach ($defaults as $type => $names) {
            foreach ($names as $name) {
                // Criar pela relação já preenche o user_id.
                $this->categories()->create(['name' => $name, 'type' => $type]);
            }
        }
    }
}
