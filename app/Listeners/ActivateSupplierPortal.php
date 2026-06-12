<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class ActivateSupplierPortal
{
    public function handle(Login $event): void
    {
        $user = $event->user;

        $user->suppliers()
            ->where('uses_portal', false)
            ->each(fn ($supplier) => $supplier->update(['uses_portal' => true]));
    }
}
