<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SiteVisit;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        $agentIds = $broker->agents()->select('users.id');
        $allMonths = collect(range(11, 0))->map(fn ($monthsAgo) => Carbon::now()->subMonths($monthsAgo));
        $sales    = $this->monthlyCounts($allMonths, Reservation::class, $agentIds, fn ($query) => $query->whereIn('status', ['confirmed', 'completed']));
        $leads    = $this->monthlyCounts($allMonths, Inquiry::class, $agentIds);
        $viewings = $this->monthlyCounts($allMonths, SiteVisit::class, $agentIds);

        $stats['sales_this_month']    = $sales->last();
        $stats['leads_this_month']    = $leads->last();
        $stats['viewings_this_month'] = $viewings->last();
        $chart = [
            'labels'   => $allMonths->map(fn ($m) => $m->format('M Y'))->values(),
            'sales'    => $sales->values(),
            'leads'    => $leads->values(),
            'viewings' => $viewings->values(),
        ];

        return view('pages.broker.dashboard', compact('stats', 'agents', 'chart'));
    }

    private function monthlyCounts($months, string $model, $agentIds, ?callable $filter = null)
    {
        return $months->map(function ($month) use ($model, $agentIds, $filter) {
            $query = $model::whereIn('broker_id', $agentIds)
                ->whereBetween('created_at', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()]);
            if ($filter) {
                $filter($query);
            }
            return $query->count();
        });
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

    public function updatePropertyTerms(Request $request, Property $property): RedirectResponse
    {
        $agentIds = auth()->user()->agents()->pluck('users.id');
        abort_unless($agentIds->contains((int) $property->broker_id), 404);

        $data = $request->validate([
            'agent_commission' => 'nullable|numeric|min:0|max:100',
            'valid_until' => 'nullable|date',
        ]);

        $property->update($data);

        return back()->with('success', 'Commission rate and validity updated.');
    }
}
