<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Inquiry;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $validated = $request->validate([
            'name' => [$user ? 'nullable' : 'required', 'string', 'max:255'],
            'message' => 'required|string|min:10|max:1000',
            'phone' => 'required|string|min:7|max:20',
            'email' => 'required|email',
            'facebook' => 'nullable|string|max:255',
        ]);

        $contactEmail = $user?->email ?? $validated['email'];
        $contactName = trim((string) ($user?->name ?? $validated['name'] ?? ''));

        // An inquiry stays a lead. Only the assigned agent turns it into a Client,
        // so typing someone's email here can never change an existing Client.
        Inquiry::create([
            'user_id' => $user?->id,
            'property_id' => $property->id,
            'broker_id' => $property->broker_id,
            'contact_name' => $contactName,
            'message' => $validated['message'],
            'phone' => $validated['phone'],
            'email' => $contactEmail,
            'facebook' => $validated['facebook'] ?? null,
            'status' => 'new',
        ]);

        \App\Models\AppNotification::create([
            'user_id' => $property->broker_id,
            'type' => 'inquiry',
            'title' => 'New property inquiry',
            'message' => $contactName . ' submitted an inquiry for ' . $property->name . '.',
            'link' => route('agent.inquiries.index'),
            'is_read' => false,
        ]);

        if ($user) {
            ChatMessage::create([
                'sender_id' => $user->id,
                'receiver_id' => $property->broker_id,
                'message' => $validated['message'],
                'sender_type' => 'user',
            ]);
        }

        return redirect()->route('client.property.show', $property->slug)
            ->with('success', 'Inquiry sent successfully! The agent will contact you soon.');
    }

    public function show(Inquiry $inquiry): View
    {
        abort_if($inquiry->user_id !== auth()->id(), 403);

        return view('pages.client.inquiries.show', compact('inquiry'));
    }
}
