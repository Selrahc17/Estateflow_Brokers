<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\CommissionAgreement;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SiteVisit;
use App\Services\BrokerComplianceService;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(BrokerComplianceService $compliance): View
    {
        $broker = auth()->user();
        $agentsQuery = $broker->agents();

        // One query for all three counts; each query is a round trip to the database.
        $team = (clone $agentsQuery)->get(['users.id', 'is_active', 'is_approved']);
        $stats = [
            'total_agents' => $team->count(),
            'active_agents' => $team->filter(fn ($agent) => $agent->is_active && $agent->is_approved)->count(),
            'pending_invites' => $team->reject(fn ($agent) => $agent->is_approved)->count(),
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

        $renewals = $compliance->renewalsNeedingAttention($broker);

        return view('pages.broker.dashboard', compact('stats', 'agents', 'chart', 'renewals'));
    }

    /**
     * Records per month for the chart, from one query instead of one per month.
     */
    private function monthlyCounts($months, string $model, $agentIds, ?callable $filter = null)
    {
        $query = $model::whereIn('broker_id', $agentIds)
            ->whereBetween('created_at', [$months->first()->copy()->startOfMonth(), $months->last()->copy()->endOfMonth()]);
        if ($filter) {
            $filter($query);
        }

        $perMonth = $query->pluck('created_at')->countBy(fn ($createdAt) => Carbon::parse($createdAt)->format('Y-m'));

        return $months->map(fn ($month) => $perMonth->get($month->format('Y-m'), 0));
    }

    public function performance(): View
    {
        $agreements = CommissionAgreement::where('broker_id', auth()->id())
            ->with('payments')
            ->get()
            ->groupBy('agent_id');

        // Counts for every agent at once, instead of three queries per agent.
        $agentIds = auth()->user()->agents()->select('users.id');
        $countByAgent = fn ($query) => $query->whereIn('broker_id', $agentIds)
            ->selectRaw('broker_id, count(*) as total')->groupBy('broker_id')->pluck('total', 'broker_id');
        $leads = $countByAgent(Inquiry::query());
        $viewings = $countByAgent(SiteVisit::query());
        $sales = $countByAgent(Reservation::whereIn('status', ['confirmed', 'completed']));

        $agents = auth()->user()->agents()->get()->map(function ($agent) use ($agreements, $leads, $viewings, $sales) {
            $agent->leads_count = (int) $leads->get($agent->id, 0);
            $agent->viewings_count = (int) $viewings->get($agent->id, 0);
            $agent->sales_count = (int) $sales->get($agent->id, 0);

            $payments = $agreements->get($agent->id, collect())->flatMap->payments;
            $agent->commission_expected = $payments->sum('agent_amount');
            $agent->commission_paid = $payments->where('payment_status', 'paid')->sum('agent_amount');

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
