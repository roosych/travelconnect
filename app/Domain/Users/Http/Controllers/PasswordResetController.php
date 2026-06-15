<?php

namespace App\Domain\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    /** Форма запроса ссылки на сброс пароля. */
    public function showLinkRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    /** Отправка письма со ссылкой на сброс. */
    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        // Не раскрываем, существует ли email: при любом исходе показываем
        // одинаковое сообщение об успехе (кроме троттлинга).
        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __('auth.reset_throttled')]);
        }

        return back()->with('status', __('auth.reset_link_sent'));
    }

    /** Форма установки нового пароля по токену. */
    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    /** Сохранение нового пароля. */
    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password): void {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', __('auth.reset_success'));
        }

        throw ValidationException::withMessages([
            'email' => [__('auth.reset_failed')],
        ]);
    }
}
