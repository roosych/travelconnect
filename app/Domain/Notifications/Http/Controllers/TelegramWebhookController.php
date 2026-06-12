<?php

namespace App\Domain\Notifications\Http\Controllers;

use App\Domain\Notifications\Services\TelegramUpdateProcessor;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TelegramWebhookController extends Controller
{
    public function handle(
        Request $request,
        string $secret,
        TelegramUpdateProcessor $processor,
    ): JsonResponse {
        $expected = (string) config('services.telegram.webhook_secret');

        // Reject unless the path secret matches (and the header, when Telegram sends one).
        if ($expected === '' || ! hash_equals($expected, $secret)) {
            abort(404);
        }

        $header = $request->header('X-Telegram-Bot-Api-Secret-Token');
        if ($header !== null && ! hash_equals($expected, $header)) {
            abort(403);
        }

        $processor->handle($request->all());

        return response()->json(['ok' => true]);
    }
}
