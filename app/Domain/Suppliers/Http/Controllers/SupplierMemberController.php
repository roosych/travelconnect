<?php

namespace App\Domain\Suppliers\Http\Controllers;

use App\Domain\Suppliers\Http\Resources\SupplierMemberResource;
use App\Domain\Suppliers\Models\Supplier;
use App\Domain\Suppliers\Services\SupplierService;
use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class SupplierMemberController extends Controller
{
    public function __construct(private readonly SupplierService $service) {}

    public function index(Supplier $supplier): AnonymousResourceCollection
    {
        $this->authorize('manageMembers', $supplier);

        $members = $supplier->users()->orderBy('name')->get();

        return SupplierMemberResource::collection($members);
    }

    public function store(Request $request, Supplier $supplier): JsonResponse
    {
        $this->authorize('manageMembers', $supplier);

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name'  => ['nullable', 'string', 'max:255'],
            'role'  => ['required', 'in:owner,manager,staff'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            $user = User::create([
                'name'     => $validated['name'] ?? str($validated['email'])->before('@')->title()->toString(),
                'email'    => $validated['email'],
                'password' => Str::random(16),
                'role'     => UserRole::Supplier,
            ]);
        }

        if ($supplier->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Пользователь уже является сотрудником этого поставщика.'], 422);
        }

        $this->service->addMember($supplier, $user, $validated['role']);

        $members = $supplier->users()->orderBy('name')->get();

        return response()->json(['data' => SupplierMemberResource::collection($members)]);
    }

    public function update(Request $request, Supplier $supplier, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $supplier);

        $validated = $request->validate([
            'role' => ['required', 'in:owner,manager,staff'],
        ]);

        $this->service->updateMemberRole($supplier, $user, $validated['role']);

        return response()->json(['message' => 'Роль обновлена.']);
    }

    public function destroy(Supplier $supplier, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $supplier);

        $member = $supplier->users()->where('user_id', $user->id)->first();

        if (! $member) {
            return response()->json(['message' => 'Пользователь не является сотрудником этого поставщика.'], 404);
        }

        if ($member->pivot->role === 'owner') {
            return response()->json(['message' => 'Нельзя удалить владельца поставщика.'], 422);
        }

        $this->service->removeMember($supplier, $user);

        return response()->json(['message' => 'Сотрудник удалён.']);
    }
}
