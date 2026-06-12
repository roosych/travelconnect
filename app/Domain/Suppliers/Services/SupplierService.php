<?php

namespace App\Domain\Suppliers\Services;

use App\Domain\Suppliers\Models\Supplier;
use App\Domain\Suppliers\Models\SupplierUser;
use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierService
{
    public function create(array $data): Supplier
    {
        return DB::transaction(function () use ($data) {
            // Читаемый пароль владельца: показываем админу один раз в модалке
            // (см. generated_password в SupplierResource), в БД хранится только хеш.
            $password = Str::password(12, letters: true, numbers: true, symbols: false);

            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => $password,
                'role'     => UserRole::Supplier,
            ]);

            $supplier = Supplier::create([
                'name'          => $data['name'],
                'email'         => $data['email'],
                'phone'         => $data['phone'] ?? null,
                'country'       => $data['country'] ?? null,
                'currency_code' => $data['currency_code'] ?? 'AZN',
                'service_types' => $data['service_types'] ?? [],
                'description'   => $data['description'] ?? null,
                'website'       => $data['website'] ?? null,
                'is_active'     => $data['is_active'] ?? true,
            ]);

            SupplierUser::create([
                'supplier_id' => $supplier->id,
                'user_id'     => $user->id,
                'role'        => 'owner',
            ]);

            // Транзиентный атрибут — не колонка, наружу уходит только в ответе store().
            $supplier->generated_password = $password;

            return $supplier;
        });
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $fields = array_filter([
            'name'          => $data['name'] ?? null,
            'email'         => $data['email'] ?? null,
            'phone'         => $data['phone'] ?? null,
            'country'       => $data['country'] ?? null,
            'currency_code' => $data['currency_code'] ?? null,
            'description'   => $data['description'] ?? null,
            'website'       => $data['website'] ?? null,
        ], fn ($v) => $v !== null);

        if (array_key_exists('service_types', $data)) {
            $fields['service_types'] = $data['service_types'];
        }

        $supplier->update($fields);

        return $supplier->fresh();
    }

    public function toggleActive(Supplier $supplier): Supplier
    {
        $supplier->update(['is_active' => !$supplier->is_active]);

        return $supplier->fresh();
    }

    public function delete(Supplier $supplier): void
    {
        $supplier->delete();
    }

    public function addMember(Supplier $supplier, User $user, string $role = 'staff'): SupplierUser
    {
        return SupplierUser::create([
            'supplier_id' => $supplier->id,
            'user_id'     => $user->id,
            'role'        => $role,
        ]);
    }

    public function updateMemberRole(Supplier $supplier, User $user, string $role): void
    {
        SupplierUser::where('supplier_id', $supplier->id)
            ->where('user_id', $user->id)
            ->update(['role' => $role]);
    }

    public function removeMember(Supplier $supplier, User $user): void
    {
        SupplierUser::where('supplier_id', $supplier->id)
            ->where('user_id', $user->id)
            ->delete();
    }
}
