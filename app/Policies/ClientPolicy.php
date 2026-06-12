<?php

namespace App\Policies;

use App\Domain\Clients\Models\Client;
use App\Domain\Users\Models\User;
use App\Policies\Concerns\BypassesForOperator;

class ClientPolicy
{
    use BypassesForOperator;

    public function view(User $user, Client $client): bool
    {
        if ($user->isAgency()) {
            return $user->agencies()->pluck('agencies.id')->contains($client->agency_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAgency();
    }

    public function update(User $user, Client $client): bool
    {
        return $this->view($user, $client);
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->view($user, $client);
    }
}
