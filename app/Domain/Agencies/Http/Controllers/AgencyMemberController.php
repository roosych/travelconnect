<?php

namespace App\Domain\Agencies\Http\Controllers;

use App\Domain\Agencies\Http\Resources\AgencyMemberResource;
use App\Domain\Agencies\Models\Agency;
use App\Domain\Agencies\Services\AgencyService;
use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;

class AgencyMemberController extends Controller
{
    public function __construct(private readonly AgencyService $service) {}

    public function index(Request $request, Agency $agency): AnonymousResourceCollection
    {
        $this->authorize('manageMembers', $agency);

        $members = $agency->users()->orderBy('name')->get();

        return AgencyMemberResource::collection($members);
    }

    public function store(Request $request, Agency $agency): JsonResponse
    {
        $this->authorize('manageMembers', $agency);
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
                'role'     => UserRole::Agency,
            ]);
        }

        if ($agency->users()->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Пользователь уже является сотрудником этого агентства.'], 422);
        }

        $this->service->addMember($agency, $user, $validated['role']);

        $members = $agency->users()->orderBy('name')->get();

        return response()->json(['data' => AgencyMemberResource::collection($members)]);
    }

    public function update(Request $request, Agency $agency, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $agency);

        $validated = $request->validate([
            'role' => ['required', 'in:owner,manager,staff'],
        ]);

        $this->service->updateMemberRole($agency, $user, $validated['role']);

        return response()->json(['message' => 'Роль обновлена.']);
    }

    public function destroy(Request $request, Agency $agency, User $user): JsonResponse
    {
        $this->authorize('manageMembers', $agency);

        $member = $agency->users()->where('user_id', $user->id)->first();

        if (! $member) {
            return response()->json(['message' => 'Пользователь не является сотрудником этого агентства.'], 404);
        }

        if ($member->pivot->role === 'owner') {
            return response()->json(['message' => 'Нельзя удалить владельца агентства.'], 422);
        }

        $this->service->removeMember($agency, $user);

        return response()->json(['message' => 'Сотрудник удалён.']);
    }
}
