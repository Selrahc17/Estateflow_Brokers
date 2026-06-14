<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\SiteVisit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteVisitController extends Controller
{
    public function index(): View
    {
        $siteVisits = SiteVisit::where('broker_id', auth()->id())
            ->with(['client', 'property', 'inquiry'])
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->latest()
            ->paginate(15);

        return view('pages.site-visits.index', compact('siteVisits'));
    }

    public function create(): View
    {
        $clients = Client::all();
        $properties = Property::all();
        $inquiries = Inquiry::where('broker_id', auth()->id())->get();

        return view('pages.site-visits.create', compact('clients', 'properties', 'inquiries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'property_id' => 'required|exists:properties,id',
            'inquiry_id'  => 'nullable|exists:inquiries,id',
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string',
            'status'       => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $data['broker_id'] = auth()->id();

        SiteVisit::create($data);

        return redirect()->route('broker.site-visits.index')->with('success', 'Site visit scheduled successfully!');
    }

    public function show(SiteVisit $siteVisit): View
    {
        abort_if($siteVisit->broker_id !== auth()->id(), 403);

        return view('pages.site-visits.show', compact('siteVisit'));
    }

    public function edit(SiteVisit $siteVisit): View
    {
        abort_if($siteVisit->broker_id !== auth()->id(), 403);

        $clients = Client::all();
        $properties = Property::all();
        $inquiries = Inquiry::where('broker_id', auth()->id())->get();

        return view('pages.site-visits.edit', compact('siteVisit', 'clients', 'properties', 'inquiries'));
    }

    public function update(Request $request, SiteVisit $siteVisit): RedirectResponse
    {
        abort_if($siteVisit->broker_id !== auth()->id(), 403);

        $data = $request->validate([
            'client_id'   => 'required|exists:clients,id',
            'property_id' => 'required|exists:properties,id',
            'inquiry_id'  => 'nullable|exists:inquiries,id',
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string',
            'status'       => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $siteVisit->update($data);

        return redirect()->route('broker.site-visits.index')->with('success', 'Site visit updated successfully!');
    }

    public function updateStatus(Request $request, SiteVisit $siteVisit): RedirectResponse
    {
        abort_if($siteVisit->broker_id !== auth()->id(), 403);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $siteVisit->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Site visit status updated successfully!');
    }
}
