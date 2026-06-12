<?php

namespace App\Domain\Users\Http\Controllers;

use App\Domain\Users\Http\Requests\LoginRequest;
use App\Domain\Users\Http\Resources\UserResource;
use App\Domain\Users\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        Auth::guard('web')->login($user);

        return response()->json([
            'success' => true,
            'data'    => [
                'token' => $token,
                'user'  => new UserResource($user),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        Auth::guard('web')->logout();

        return response()->json([
            'success' => true,
            'data'    => ['message' => 'Logged out successfully.'],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new UserResource($request->user()),
        ]);
    }
}
