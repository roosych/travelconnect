<?php

namespace App\Domain\Users\Http\Controllers;

use App\Domain\Users\Enums\UserRole;
use App\Domain\Users\Http\Requests\StoreOperatorRequest;
use App\Domain\Users\Http\Requests\UpdateOperatorRequest;
use App\Domain\Users\Http\Resources\OperatorResource;
use App\Domain\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OperatorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403);
        }

        $query = User::where('role', UserRole::Operator)->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        $operators = $query->get();

        return response()->json([
            'success' => true,
            'data'    => OperatorResource::collection($operators),
        ]);
    }

    public function store(StoreOperatorRequest $request): JsonResponse
    {
        $plain = Str::password(16);

        $user = User::create([
            'name'     => $request->validated('name'),
            'email'    => $request->validated('email'),
            'phone'    => $request->validated('phone'),
            'password' => $plain,
            'role'     => UserRole::Operator,
        ]);

        return response()->json([
            'success' => true,
            'data'    => (new OperatorResource($user))->withPassword($plain),
        ], 201);
    }

    public function update(UpdateOperatorRequest $request, User $operator): JsonResponse
    {
        abort_unless($operator->role === UserRole::Operator, 404);

        $operator->update($request->validated());

        return response()->json([
            'success' => true,
            'data'    => new OperatorResource($operator->fresh()),
        ]);
    }

    public function resetPassword(Request $request, User $operator): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403);
        }

        abort_unless($operator->role === UserRole::Operator, 404);

        $plain = Str::password(16);
        $operator->update(['password' => $plain]);

        return response()->json([
            'success'        => true,
            'plain_password' => $plain,
        ]);
    }

    public function destroy(Request $request, User $operator): JsonResponse
    {
        if (! $request->user()->isOperator()) {
            abort(403);
        }

        abort_unless($operator->role === UserRole::Operator, 404);

        if ($operator->id === $request->user()->id) {
            return response()->json(['message' => 'Нельзя удалить собственную учётную запись.'], 422);
        }

        $operator->delete();

        return response()->json(['success' => true]);
    }
}
