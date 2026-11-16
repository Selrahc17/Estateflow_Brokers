<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\SiteVisit;
use App\Support\Totals;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class SiteVisitController extends Controller
{
    /**
     * Upcoming visits first, soonest at the top, so the agent sees what is next.
     * "Past" covers visits that already happened, were completed or cancelled.
     */
    public function index(Request $request): View
    {
        $mine = fn () => SiteVisit::where('broker_id', auth()->id());
        $when = in_array($request->input('when'), ['upcoming', 'past', 'all'], true) ? $request->input('when') : 'upcoming';
        $status = in_array($request->input('status'), ['pending', 'confirmed', 'completed', 'cancelled'], true) ? $request->input('status') : null;
        $open = ['pending', 'confirmed'];

        $stats = Totals::of([
            'today' => $mine()->whereIn('status', $open)->whereBetween('scheduled_at', [now()->startOfDay(), now()->endOfDay()])->selectRaw('count(*)'),
            'week' => $mine()->whereIn('status', $open)->whereBetween('scheduled_at', [now(), now()->addDays(7)->endOfDay()])->selectRaw('count(*)'),
            'unconfirmed' => $mine()->where('status', 'pending')->where('scheduled_at', '>=', now()->startOfDay())->selectRaw('count(*)'),
            'completed' => $mine()->where('status', 'completed')->where('scheduled_at', '>=', now()->startOfMonth())->selectRaw('count(*)'),
        ]);

        $siteVisits = $mine()
            ->with(['client', 'property', 'inquiry'])
            ->when($when === 'upcoming', fn ($query) => $query->whereIn('status', $open)->where('scheduled_at', '>=', now()->startOfDay())->orderBy('scheduled_at'))
            ->when($when === 'past', fn ($query) => $query->where(fn ($past) => $past->where('scheduled_at', '<', now()->startOfDay())->orWhereIn('status', ['completed', 'cancelled']))->orderByDesc('scheduled_at'))
            ->when($when === 'all', fn ($query) => $query->orderByDesc('scheduled_at'))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->paginate(20)
            ->withQueryString();

        return view('pages.site-visits.index', compact('siteVisits', 'stats', 'when', 'status'));
    }

    public function create(): View
    {
        $clients = Client::where('broker_id', auth()->id())->get();
        $properties = Property::where('broker_id', auth()->id())->get();
        $inquiries = Inquiry::where('broker_id', auth()->id())->get();

        return view('pages.site-visits.create', compact('clients', 'properties', 'inquiries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'   => ['required', Rule::exists('clients', 'id')->where('broker_id', auth()->id())],
            'property_id' => ['required', Rule::exists('properties', 'id')->where('broker_id', auth()->id())],
            'inquiry_id'  => ['nullable', Rule::exists('inquiries', 'id')->where('broker_id', auth()->id())],
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string',
            'status'       => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $data['broker_id'] = auth()->id();

        SiteVisit::create($data);

        return redirect()->route('agent.site-visits.index')->with('success', 'Site visit scheduled successfully!');
    }

    public function show(SiteVisit $siteVisit): View
    {
        abort_if($siteVisit->broker_id !== auth()->id(), 403);

        return view('pages.site-visits.show', compact('siteVisit'));
    }

    public function edit(SiteVisit $siteVisit): View
    {
        abort_if($siteVisit->broker_id !== auth()->id(), 403);

        $clients = Client::where('broker_id', auth()->id())->get();
        $properties = Property::where('broker_id', auth()->id())->get();
        $inquiries = Inquiry::where('broker_id', auth()->id())->get();

        return view('pages.site-visits.edit', compact('siteVisit', 'clients', 'properties', 'inquiries'));
    }

    public function update(Request $request, SiteVisit $siteVisit): RedirectResponse
    {
        abort_if($siteVisit->broker_id !== auth()->id(), 403);

        $data = $request->validate([
            'client_id'   => ['required', Rule::exists('clients', 'id')->where('broker_id', auth()->id())],
            'property_id' => ['required', Rule::exists('properties', 'id')->where('broker_id', auth()->id())],
            'inquiry_id'  => ['nullable', Rule::exists('inquiries', 'id')->where('broker_id', auth()->id())],
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string',
            'status'       => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $siteVisit->update($data);

        return redirect()->route('agent.site-visits.index')->with('success', 'Site visit updated successfully!');
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
