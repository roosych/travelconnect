<?php

namespace App\Domain\Users\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public function __construct(public string $token) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        // Письмо отправляем на языке получателя.
        $locale = $notifiable->locale ?? config('app.locale');

        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

        return (new MailMessage)
            ->subject(Lang::get('auth.reset_email_subject', [], $locale))
            ->greeting(Lang::get('auth.reset_email_greeting', [], $locale))
            ->line(Lang::get('auth.reset_email_intro', [], $locale))
            ->action(Lang::get('auth.reset_email_action', [], $locale), $url)
            ->line(Lang::get('auth.reset_email_expire', ['count' => $expire], $locale))
            ->line(Lang::get('auth.reset_email_ignore', [], $locale));
    }
}
