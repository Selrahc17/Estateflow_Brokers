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
        $siteVisits = SiteVisit::whereIn('client_id', auth()->user()->clientProfiles()->select('id'))
            ->with(['property', 'broker'])
            ->latest()
            ->paginate(10);

        return view('pages.client.site-visits.index', compact('siteVisits'));
    }

    public function store(Request $request, Property $property): RedirectResponse
    {
        $client = auth()->user()->clientProfiles()->where('broker_id', $property->broker_id)->first();
        if (!$client) {
            return back()->withErrors([
                'site_visit' => 'Send an inquiry for this property first. Its agent needs to start reviewing your requirements.',
            ]);
        }

        if ($client->qualification_status !== 'under_review') {
            return back()->withErrors([
                'site_visit' => 'A site visit can be requested after the agent starts reviewing your requirements.',
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
        abort_unless(auth()->user()->clientProfiles()->whereKey($siteVisit->client_id)->exists(), 403);
    }
}
