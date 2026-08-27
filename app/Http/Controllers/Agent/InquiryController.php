<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(): View
    {
        $inquiries = Inquiry::where('broker_id', auth()->id())
            ->with(['user', 'property'])
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->latest()
            ->paginate(12);

        $statuses = ['new', 'contacted', 'site_visit_scheduled', 'negotiating', 'closed', 'lost'];

        return view('pages.agent.inquiries.index', compact('inquiries', 'statuses'));
    }

    public function show(Inquiry $inquiry): View
    {
        abort_if($inquiry->broker_id !== auth()->id(), 403);

        return view('pages.agent.inquiries.show', compact('inquiry'));
    }

    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        abort_if($inquiry->broker_id !== auth()->id(), 403);

        $validated = $request->validate([
            'status' => 'required|in:new,contacted,site_visit_scheduled,negotiating,closed,lost',
        ]);

        $inquiry->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Inquiry status updated successfully.');
    }
}
