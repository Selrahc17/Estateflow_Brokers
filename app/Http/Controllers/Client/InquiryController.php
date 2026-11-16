<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Inquiry;
use App\Models\Property;
use App\Notifications\NewInquiry;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InquiryController extends Controller
{
    public function index(): View
    {
        $inquiries = auth()->user()->inquiries()
            ->with(['property', 'broker'])
            ->latest()
            ->paginate(12);

        return view('pages.client.inquiries.index', compact('inquiries'));
    }

    public function store(Request $request, Property $property): RedirectResponse
    {
        $user = auth()->user();

        $isViewing = $request->input('type') === 'viewing';

        $validated = $request->validate([
            'type' => 'nullable|in:inquiry,viewing',
            'name' => [$user ? 'nullable' : 'required', 'string', 'max:255'],
            'message' => 'required|string|min:10|max:1000',
            'phone' => 'required|string|min:7|max:20',
            'email' => 'required|email',
            'facebook' => 'nullable|string|max:255',
            'preferred_date' => [$isViewing ? 'required' : 'nullable', 'date', 'after_or_equal:today'],
            'preferred_time' => [$isViewing ? 'required' : 'nullable', 'date_format:H:i'],
        ], [
            'preferred_date.required' => 'Choose the day you would like to view the property.',
            'preferred_date.after_or_equal' => 'Choose today or a later day.',
            'preferred_time.required' => 'Choose a time for the viewing.',
        ]);

        $preferredAt = null;
        if ($isViewing) {
            $preferredAt = Carbon::parse("{$validated['preferred_date']} {$validated['preferred_time']}");
            if ($preferredAt->isPast()) {
                throw ValidationException::withMessages(['preferred_time' => 'Choose a time later than now.']);
            }
        }

        $contactEmail = $user?->email ?? $validated['email'];
        $contactName = trim((string) ($user?->name ?? $validated['name'] ?? ''));

        // An inquiry stays a lead. Only the assigned agent turns it into a Client,
        // so typing someone's email here can never change an existing Client.
        $inquiry = Inquiry::create([
            'user_id' => $user?->id,
            'property_id' => $property->id,
            'broker_id' => $property->broker_id,
            'type' => $isViewing ? 'viewing' : 'inquiry',
            'contact_name' => $contactName,
            'message' => $validated['message'],
            'preferred_viewing_at' => $preferredAt,
            'phone' => $validated['phone'],
            'email' => $contactEmail,
            'facebook' => $validated['facebook'] ?? null,
            'status' => 'new',
        ]);

        \App\Models\AppNotification::create([
            'user_id' => $property->broker_id,
            'type' => 'inquiry',
            'title' => $isViewing ? 'New viewing request' : 'New property inquiry',
            'message' => $isViewing
                ? "{$contactName} would like to view {$property->name} on " . $preferredAt->format('D, M j \a\t g:i A') . '.'
                : "{$contactName} submitted an inquiry for {$property->name}.",
            'link' => route('agent.inquiries.show', $inquiry),
            'is_read' => false,
        ]);

        // Also email the agent, after the buyer's page has loaded.
        $this->emailAgent($property->broker, new NewInquiry($inquiry->load('property')));

        if ($user) {
            ChatMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $property->broker_id,
                'message' => $validated['message'],
                'sender_type' => 'user',
            ]);
        }

        $agent = $property->broker?->name ?? 'The agent';

        return redirect()->route('client.property.show', $property->slug)
            ->with('success', $isViewing
                ? "Viewing request sent! {$agent} will confirm the schedule with you soon."
                : "Inquiry sent! {$agent} will contact you soon.");
    }

    public function show(Inquiry $inquiry): View
    {
        abort_if($inquiry->user_id !== auth()->id(), 403);

        return view('pages.client.inquiries.show', compact('inquiry'));
    }
}
