<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Property;
use App\Models\SiteVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteVisitController extends Controller
{
    public function index(): View
    {
        $client = auth()->user()->clientProfile;
        $siteVisits = $client
            ? SiteVisit::where('client_id', $client->id)->with(['property', 'broker'])->latest()->paginate(10)
            : collect();

        return view('pages.client.site-visits.index', compact('siteVisits'));
    }

    public function store(Request $request, Property $property): RedirectResponse
    {
        $client = auth()->user()->clientProfile;
        if (!$client) {
            return back()->withErrors([
                'site_visit' => 'A client profile is required before requesting an appointment.',
            ]);
        }

        $data = $request->validate([
            'scheduled_at' => 'required|date|after:now',
            'notes' => 'nullable|string|max:1000',
        ]);

        SiteVisit::create([
            'client_id' => $client->id,
            'property_id' => $property->id,
            'broker_id' => $property->broker_id,
            'scheduled_at' => $data['scheduled_at'],
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        return back()->with('success', 'Appointment request submitted successfully.');
    }

    public function reschedule(Request $request, SiteVisit $siteVisit): RedirectResponse
    {
        $this->ensureClientOwnership($siteVisit);

        if (!in_array($siteVisit->status, ['pending', 'confirmed'], true)) {
            return back()->withErrors(['site_visit' => 'Only pending or confirmed visits can be rescheduled.']);
        }

        $data = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        $siteVisit->update([
            'scheduled_at' => $data['scheduled_at'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Appointment reschedule request submitted.');
    }

    public function cancel(SiteVisit $siteVisit): RedirectResponse
    {
        $this->ensureClientOwnership($siteVisit);

        if (!in_array($siteVisit->status, ['pending', 'confirmed'], true)) {
            return back()->withErrors(['site_visit' => 'Only pending or confirmed visits can be cancelled.']);
        }

        $siteVisit->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment cancelled successfully.');
    }

    private function ensureClientOwnership(SiteVisit $siteVisit): void
    {
        abort_unless($siteVisit->client_id === auth()->user()->clientProfile?->id, 403);
    }
}
