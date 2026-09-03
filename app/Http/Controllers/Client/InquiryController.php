<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
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
        $validated = $request->validate([
            'message' => 'required|string|min:10|max:1000',
            'phone' => 'required|string|min:7|max:20',
            'email' => 'required|email',
        ]);

        $user = auth()->user();
        $contactEmail = $user?->email ?? $validated['email'];
        $client = $user
            ? Client::where('user_id', $user->id)->first()
            : Client::where('email', $validated['email'])->first();

        if (! $client) {
            $client = new Client();
        }

        $client->fill([
            'user_id' => $user?->id ?? $client->user_id,
            'broker_id' => $property->broker_id,
            'first_name' => $client->first_name ?: $this->extractFirstName($contactEmail),
            'last_name' => $client->last_name ?: $this->extractLastName($contactEmail),
            'email' => $contactEmail,
            'phone' => $validated['phone'],
            'status' => 'active',
            'password' => $client->password ?: Hash::make(Str::random(24)),
        ]);

        $client->save();

        Inquiry::create([
            'user_id' => auth()->id(),
            'property_id' => $property->id,
            'broker_id' => $property->broker_id,
            'message' => $validated['message'],
            'phone' => $validated['phone'],
            'email' => $contactEmail,
            'status' => 'new',
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
            ->with('success', 'Inquiry sent successfully! The broker will contact you soon.');
    }

    public function show(Inquiry $inquiry): View
    {
        abort_if($inquiry->user_id !== auth()->id(), 403);

        return view('pages.client.inquiries.show', compact('inquiry'));
    }

    private function extractFirstName(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? 'client';
        $localPart = str_replace(['.', '_', '-'], ' ', $localPart);

        return ucfirst(trim(explode(' ', $localPart)[0] ?? 'Client'));
    }

    private function extractLastName(string $email): string
    {
        $localPart = explode('@', $email)[0] ?? 'client';
        $localPart = str_replace(['.', '_', '-'], ' ', $localPart);
        $parts = preg_split('/\s+/', trim($localPart)) ?: [''];

        return ucfirst($parts[1] ?? '');
    }
}
