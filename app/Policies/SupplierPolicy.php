<?php

namespace App\Policies;

use App\Domain\Suppliers\Models\Supplier;
use App\Domain\Users\Models\User;
use App\Policies\Concerns\BypassesForOperator;

class SupplierPolicy
{
    use BypassesForOperator;

    // Supplier CRUD is operator-only. Operators bypass via BypassesForOperator::before();
    // every other role falls through to these methods and is denied.
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Supplier $supplier): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return false;
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return false;
    }

    // A supplier's own staff may manage that supplier's members (mirror of AgencyPolicy).
    public function manageMembers(User $user, Supplier $supplier): bool
    {
        return $this->belongsToSupplier($user, $supplier);
    }

    // A supplier's own staff may manage that supplier's service catalog.
    public function manageCatalog(User $user, Supplier $supplier): bool
    {
        return $this->belongsToSupplier($user, $supplier);
    }

    // Incidents are internal quality records: operators (bypass) and the
    // supplier's own staff may read them.
    public function viewIncidents(User $user, Supplier $supplier): bool
    {
        return $this->belongsToSupplier($user, $supplier);
    }

    // Suppliers may set their own logo/avatar (operators bypass via before()).
    public function updateAvatar(User $user, Supplier $supplier): bool
    {
        return $this->belongsToSupplier($user, $supplier);
    }

    private function belongsToSupplier(User $user, Supplier $supplier): bool
    {
        if ($user->isSupplier()) {
            return $user->suppliers()->pluck('suppliers.id')->contains($supplier->id);
        }

        return false;
    }
}
