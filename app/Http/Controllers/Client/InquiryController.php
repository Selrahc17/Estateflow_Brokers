<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
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
        $validated = $request->validate([
            'message' => 'required|string|min:10|max:1000',
            'phone' => 'required|string|min:7|max:20',
            'email' => 'required|email',
        ]);

        Inquiry::create([
            'user_id' => auth()->id(),
            'property_id' => $property->id,
            'broker_id' => $property->broker_id,
            'message' => $validated['message'],
            'phone' => $validated['phone'],
            'email' => $validated['email'],
            'status' => 'new',
        ]);

        return redirect()->route('client.property.show', $property->slug)
            ->with('success', 'Inquiry sent successfully! The broker will contact you soon.');
    }

    public function show(Inquiry $inquiry): View
    {
        abort_if($inquiry->user_id !== auth()->id(), 403);

        return view('pages.client.inquiries.show', compact('inquiry'));
    }
}
