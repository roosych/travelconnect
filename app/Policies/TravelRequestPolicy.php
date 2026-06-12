<?php

namespace App\Policies;

use App\Domain\Requests\Enums\RequestStatus;
use App\Domain\Requests\Models\TravelRequest;
use App\Domain\Users\Models\User;

class TravelRequestPolicy
{
    /**
     * Операторы по умолчанию обходят политику (как BypassesForOperator), НО не
     * должны видеть/редактировать черновики агентств — это приватная стадия
     * подготовки заявки. Возврат false жёстко запрещает доступ даже оператору.
     */
    public function before(User $user, string $ability, $request = null): bool|null
    {
        if (! $user->isOperator()) {
            return null;
        }

        if ($request instanceof TravelRequest
            && $request->status === RequestStatus::Draft
            && in_array($ability, ['view', 'update'], true)) {
            return false;
        }

        return true;
    }

    public function view(User $user, TravelRequest $request): bool
    {
        if ($user->isAgency()) {
            return $user->agencies()->pluck('agencies.id')->contains($request->agency_id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->isAgency();
    }

    public function update(User $user, TravelRequest $request): bool
    {
        return $this->view($user, $request);
    }
}
