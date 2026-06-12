<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

/**
 * Overrides HTTP-layer error responses to follow the standard API envelope:
 * { success, message, errors? }
 *
 * Domain exceptions (BusinessRuleException, InvalidStatusTransitionException)
 * are registered in bootstrap/app.php via $exceptions->render().
 */
class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    protected function unauthenticated($request, AuthenticationException $exception): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            return redirect()->guest(route('login'));
        }

        return response()->json([
            'success' => false,
            'message' => 'Unauthenticated.',
        ], 401);
    }

    protected function invalidJson($request, ValidationException $exception): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors'  => $exception->errors(),
        ], 422);
    }
}
