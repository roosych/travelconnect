<?php

namespace App\Domain\Notifications\Http\Controllers;

use App\Domain\Notifications\Enums\NotificationCategory;
use App\Domain\Notifications\Enums\NotificationChannel;
use App\Domain\Notifications\Services\NotificationPreferenceService;
use App\Domain\Notifications\Services\TelegramLinkService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationSettingsController extends Controller
{
    public function __construct(
        private readonly NotificationPreferenceService $preferences,
        private readonly TelegramLinkService $links,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'categories' => array_map(
                fn (NotificationCategory $c) => [
                    'key' => $c->value,
                    'label' => $c->label(),
                    'description' => $c->description(),
                ],
                $this->preferences->categoriesForUser($user),
            ),
            'channels' => array_map(
                fn (NotificationChannel $c) => ['key' => $c->value, 'label' => $c->label()],
                NotificationChannel::cases(),
            ),
            'matrix' => $this->preferences->matrixFor($user),
            'telegram' => [
                'linked' => $user->isTelegramLinked(),
                'username' => $user->telegram_username,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'matrix' => ['required', 'array'],
            'matrix.*' => ['array'],
            'matrix.*.*' => ['boolean'],
        ]);

        $this->preferences->update($request->user(), $validated['matrix']);

        return response()->json(['success' => true]);
    }

    public function telegramLink(Request $request): JsonResponse
    {
        return response()->json([
            'url' => $this->links->linkUrl($request->user()),
        ]);
    }

    public function telegramUnlink(Request $request): JsonResponse
    {
        $this->links->unlink($request->user());

        return response()->json(['success' => true]);
    }
}
