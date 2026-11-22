<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

/**
 * Policy: regras de "quem pode fazer o quê" com um lançamento.
 *
 * O Laravel encontra esta classe sozinho pelo nome (Transaction + Policy).
 * É chamada por Gate::authorize('update', $transaction) nos controllers e por
 * $user->can('update', $transaction) nos Form Requests.
 */
class TransactionPolicy
{
    /** Só o dono pode editar. */
    public function update(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id;
    }

    /** Só o dono pode apagar. */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $transaction->user_id === $user->id;
    }
}
