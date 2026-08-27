<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SiteVisit;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $broker = auth()->user();
        $agentsQuery = $broker->agents();

        $stats = [
            'total_agents' => (clone $agentsQuery)->count(),
            'active_agents' => (clone $agentsQuery)->where('is_active', true)->count(),
            'pending_agents' => (clone $agentsQuery)->where('is_approved', false)->count(),
        ];

        $agents = $agentsQuery
            ->withCount(['properties', 'clients'])
            ->latest()
            ->paginate(15);

        return view('pages.broker.dashboard', compact('stats', 'agents'));
    }

    public function performance(): View
    {
        $agents = auth()->user()->agents()->get()->map(function ($agent) {
            $agent->leads_count = Inquiry::where('broker_id', $agent->id)->count();
            $agent->viewings_count = SiteVisit::where('broker_id', $agent->id)->count();
            $agent->sales_count = Reservation::where('broker_id', $agent->id)
                ->whereIn('status', ['confirmed', 'completed'])
                ->count();
            return $agent;
        });

        return view('pages.broker.performance', compact('agents'));
    }

    public function propertyLists(): View
    {
        $agentIds = auth()->user()->agents()->select('users.id');
        $properties = Property::whereIn('broker_id', $agentIds)
            ->with('broker')
            ->latest()
            ->paginate(12);

        return view('pages.broker.properties.index', compact('properties'));
    }
}
