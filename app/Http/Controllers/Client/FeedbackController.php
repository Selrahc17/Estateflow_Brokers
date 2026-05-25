<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    public function index(): View
    {
        $feedbacks = Feedback::where('user_id', auth()->id())->latest()->paginate(10);
        return view('pages.client.feedback.index', compact('feedbacks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'type'    => 'required|in:general,complaint,suggestion,appreciation',
            'rating'  => 'nullable|integer|min:1|max:5',
        ]);

        $data['user_id'] = auth()->id();
        $data['status'] = 'pending';

        Feedback::create($data);

        return redirect()->route('client.account.feedback')->with('success', 'Feedback submitted. Thank you!');
    }
}