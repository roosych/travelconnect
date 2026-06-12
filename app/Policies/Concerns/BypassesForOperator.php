<?php

namespace App\Policies\Concerns;

use App\Domain\Users\Models\User;

trait BypassesForOperator
{
    public function before(User $user, string $ability): bool|null
    {
        return $user->isOperator() ? true : null;
    }
}
