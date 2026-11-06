<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(): View
    {
        $feedbacks = Feedback::with('client', 'user')
            ->when(request('status'), fn ($query) => $query->where('status', request('status')))
            ->when(request('rating'), fn ($query) => $query->where('rating', (int) request('rating')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $stats = [
            'total' => Feedback::count(),
            'pending' => Feedback::where('status', 'pending')->count(),
            'resolved' => Feedback::where('status', 'resolved')->count(),
            'average' => Feedback::whereNotNull('rating')->avg('rating'),
        ];

        return view('pages.admin.feedback.index', compact('feedbacks', 'stats'));
    }

    public function resolve(Feedback $feedback): RedirectResponse
    {
        $feedback->update(['status' => 'resolved', 'resolved_at' => now()]);

        if ($feedback->user_id) {
            AppNotification::create([
                'user_id' => $feedback->user_id,
                'type' => 'feedback_resolved',
                'title' => 'Your feedback was resolved',
                'message' => "We have reviewed \"{$feedback->subject}\". Thank you for letting us know.",
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Feedback marked as resolved.');
    }
}
