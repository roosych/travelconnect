<?php

namespace App\Domain\Agencies\Services;

use App\Domain\Agencies\Models\Agency;
use App\Domain\Agencies\Models\AgencyUser;
use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AgencyService
{
    public function create(array $data): Agency
    {
        return DB::transaction(function () use ($data) {
            // Читаемый пароль владельца: показываем админу один раз в модалке
            // (см. generated_password в AgencyResource), в БД хранится только хеш.
            $password = Str::password(12, letters: true, numbers: true, symbols: false);

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $password,
                'role'     => UserRole::Agency,
            ]);

            $agency = Agency::create([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] ?? null,
                'country'       => $data['country'] ?? null,
                'currency_code' => $data['currency_code'] ?? 'AZN',
            ]);

            AgencyUser::create([
                'agency_id' => $agency->id,
                'user_id'   => $user->id,
                'role'      => 'owner',
            ]);

            // Транзиентный атрибут — не колонка, наружу уходит только в ответе store().
            $agency->generated_password = $password;

            return $agency;
        });
    }

    public function update(Agency $agency, array $data): Agency
    {
        $agency->update(array_filter([
            'name'          => $data['name'] ?? null,
            'email'         => $data['email'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'country'       => $data['country'] ?? null,
            'currency_code' => $data['currency_code'] ?? null,
        ], fn ($v) => $v !== null));

        return $agency->fresh();
    }

    public function delete(Agency $agency): void
    {
        $agency->delete();
    }

    public function addMember(Agency $agency, User $user, string $role = 'staff'): AgencyUser
    {
        return AgencyUser::create([
            'agency_id' => $agency->id,
            'user_id'   => $user->id,
            'role'      => $role,
        ]);
    }

    public function updateMemberRole(Agency $agency, User $user, string $role): void
    {
        AgencyUser::where('agency_id', $agency->id)
            ->where('user_id', $user->id)
            ->update(['role' => $role]);
    }

    public function removeMember(Agency $agency, User $user): void
    {
        AgencyUser::where('agency_id', $agency->id)
            ->where('user_id', $user->id)
            ->delete();
    }
}
