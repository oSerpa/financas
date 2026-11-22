<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

/**
 * Policy: regras de "quem pode fazer o quê" com uma categoria.
 *
 * O Laravel encontra esta classe sozinho pelo nome (Category + Policy).
 * É chamada por Gate::authorize('update', $category) nos controllers e por
 * $user->can('update', $category) nos Form Requests.
 */
class CategoryPolicy
{
    /** Só o dono pode editar. */
    public function update(User $user, Category $category): bool
    {
        return $category->user_id === $user->id;
    }

    /** Só o dono pode apagar. */
    public function delete(User $user, Category $category): bool
    {
        return $category->user_id === $user->id;
    }
}
