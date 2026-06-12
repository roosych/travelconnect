<?php

namespace App\Policies;

use App\Domain\Agencies\Models\Agency;
use App\Domain\Users\Models\User;
use App\Policies\Concerns\BypassesForOperator;

class AgencyPolicy
{
    use BypassesForOperator;

    // Agency CRUD is operator-only. Operators bypass via BypassesForOperator::before();
    // every other role falls through to these methods and is denied.
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Agency $agency): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Agency $agency): bool
    {
        return false;
    }

    public function delete(User $user, Agency $agency): bool
    {
        return false;
    }

    public function manageMembers(User $user, Agency $agency): bool
    {
        if ($user->isAgency()) {
            return $user->agencies()->pluck('agencies.id')->contains($agency->id);
        }

        return false;
    }

    // Agencies may set their own logo/avatar (operators bypass via before()).
    public function updateAvatar(User $user, Agency $agency): bool
    {
        if ($user->isAgency()) {
            return $user->agencies()->pluck('agencies.id')->contains($agency->id);
        }

        return false;
    }
}
