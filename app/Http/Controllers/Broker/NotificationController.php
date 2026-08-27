<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = AppNotification::where('user_id', auth()->id())
            ->latest()
            ->paginate(20);
        $agents = auth()->user()->agents()->orderBy('name')->get();

        return view('pages.broker.notifications.index', compact('notifications', 'agents'));
    }

    public function markRead(AppNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) auth()->id(), 403);
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
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(function ($query) {
                $query->where('role', 'agent')->where('broker_id', auth()->id());
            })],
            'title' => 'required|string|max:255',
            'message' => 'nullable|string|max:2000',
        ]);

        AppNotification::create([
            'user_id' => $data['user_id'],
            'type' => 'broker',
            'title' => $data['title'],
            'message' => $data['message'] ?? null,
        ]);

        return back()->with('success', 'Notification sent to the Agent.');
    }
}
