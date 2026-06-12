<?php

namespace App\Domain\Notifications\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    /** Recent in-app notifications for the bell + unread count. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $items = $user->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn (DatabaseNotification $n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? '',
                'message' => $n->data['message'] ?? '',
                'url' => $n->data['url'] ?? null,
                'icon' => $n->data['icon'] ?? 'ki-notification',
                'category' => $n->data['category'] ?? null,
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at->toIso8601String(),
            ]);

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'data' => $items,
        ]);
    }

    /**
     * Full notifications feed for the dedicated page: filterable history with
     * per-category counts. Unlike the bell (capped at 20), this is paginated.
     */
    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();

        // Base query — search + date range + read/unread, but NOT category
        // (category is the chip dimension, counted separately below). reorder()
        // drops the relation's default created_at sort so the grouped count below
        // doesn't trip Postgres' GROUP BY rule; the list re-adds latest().
        $base = $user->notifications()->getQuery()->reorder();

        if ($request->input('status') === 'unread') {
            $base->whereNull('read_at');
        } elseif ($request->input('status') === 'read') {
            $base->whereNotNull('read_at');
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $base->where(function ($q) use ($s) {
                $q->whereRaw("(data::jsonb)->>'title' ILIKE ?", ["%{$s}%"])
                  ->orWhereRaw("(data::jsonb)->>'message' ILIKE ?", ["%{$s}%"]);
            });
        }

        if ($request->filled('from')) {
            $base->whereDate('created_at', '>=', $request->date('from'));
        }
        if ($request->filled('to')) {
            $base->whereDate('created_at', '<=', $request->date('to'));
        }

        // Chip counts over the base (before category narrowing).
        $byCategory = (clone $base)
            ->selectRaw("(data::jsonb)->>'category' as cat, count(*) as c")
            ->groupBy('cat')
            ->pluck('c', 'cat');

        $allCount = (clone $base)->count();

        // Narrow by the selected category for the actual paginated list.
        $list = (clone $base)->latest();
        if ($request->filled('category')) {
            $list->whereRaw("(data::jsonb)->>'category' = ?", [$request->input('category')]);
        }

        $page = $list->paginate($request->integer('per_page', 30));

        return response()->json([
            'data' => collect($page->items())->map(fn (DatabaseNotification $n) => [
                'id' => $n->id,
                'title' => $n->data['title'] ?? '',
                'message' => $n->data['message'] ?? '',
                'url' => $n->data['url'] ?? null,
                'icon' => $n->data['icon'] ?? 'ki-notification',
                'category' => $n->data['category'] ?? null,
                'read' => $n->read_at !== null,
                'created_at' => $n->created_at->toIso8601String(),
            ]),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
                'counts' => [
                    'all' => $allCount,
                    'by_category' => $byCategory,
                ],
                'unread_total' => $user->unreadNotifications()->count(),
            ],
        ]);
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark notifications read. With no params marks all unread (used by the bell);
     * an optional category scopes it to one type (used by the feed page).
     */
    public function markAllRead(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = $user->unreadNotifications();

        if ($request->filled('category')) {
            $query->whereRaw("(data::jsonb)->>'category' = ?", [$request->input('category')]);
        }

        $query->get()->markAsRead();

        return response()->json([
            'success' => true,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }
}
