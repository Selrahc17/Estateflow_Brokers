<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(): View
    {
        $feedbacks = Feedback::with('client', 'user')->latest()->paginate(15);
        return view('pages.admin.feedback.index', compact('feedbacks'));
    }

    public function resolve(Feedback $feedback): RedirectResponse
    {
        $feedback->update(['status' => 'resolved', 'resolved_at' => now()]);
        return back()->with('success', 'Feedback marked as resolved.');
    }
}