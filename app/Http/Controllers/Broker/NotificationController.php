<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AppNotification::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);

        return view('pages.notifications.index', compact('notifications'));
    }

    public function markRead(AppNotification $notification): RedirectResponse
    {
        $notification->update(['is_read' => true, 'read_at' => now()]);
        return back();
    }

    public function markAllRead(): RedirectResponse
    {
        AppNotification::where('user_id', auth()->id())->unread()->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'title'   => 'required|string|max:255',
            'message' => 'nullable|string',
        ]);

        AppNotification::create([
            'user_id' => $data['user_id'],
            'type'    => 'system',
            'title'   => $data['title'],
            'message' => $data['message'] ?? null,
        ]);

        return back()->with('success', 'Notification sent.');
    }
}