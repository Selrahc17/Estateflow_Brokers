<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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

        $client = Client::where('email', $inquiry->email)->where('broker_id', auth()->id())->first();

        return view('pages.agent.inquiries.show', compact('inquiry', 'client'));
    }

    public function convertToClient(Inquiry $inquiry): RedirectResponse
    {
        abort_if($inquiry->broker_id !== auth()->id(), 403);

        // Client emails are unique across all Brokers, so soft-deleted rows count too.
        $existing = Client::withTrashed()->where('email', $inquiry->email)->first();

        if ($existing) {
            if ((int) $existing->broker_id === (int) auth()->id() && ! $existing->trashed()) {
                return redirect()->route('agent.clients.show', $existing);
            }

            // A registered buyer has no agent yet. The where clause keeps two agents from claiming them at once.
            $claimed = ! $existing->trashed()
                && Client::whereKey($existing->id)->whereNull('broker_id')->update(['broker_id' => auth()->id()]);

            if (! $claimed) {
                return back()->with('error', 'This buyer cannot be added because their email is already linked to another account.');
            }

            return redirect()->route('agent.clients.show', $existing)->with('success', 'Inquiry added to your clients.');
        }

        [$firstName, $lastName] = $this->nameParts($inquiry);

        $client = Client::create([
            'user_id' => $inquiry->user_id,
            'broker_id' => auth()->id(),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $inquiry->email,
            'phone' => $inquiry->phone,
            'password' => Hash::make(Str::random(24)),
            'status' => 'active',
        ]);

        return redirect()->route('agent.clients.show', $client)->with('success', 'Inquiry added to your clients.');
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function nameParts(Inquiry $inquiry): array
    {
        $name = trim((string) ($inquiry->contact_name ?: $inquiry->user?->name));

        if ($name === '') {
            $name = str_replace(['.', '_', '-'], ' ', Str::before((string) $inquiry->email, '@'));
        }

        $parts = preg_split('/\s+/', trim($name), 2) ?: [''];

        return [Str::ucfirst($parts[0] ?: 'Client'), Str::ucfirst($parts[1] ?? '')];
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
