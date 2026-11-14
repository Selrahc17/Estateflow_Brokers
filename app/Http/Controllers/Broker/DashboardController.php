<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Sale;
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
        // Sales are the commission records, counted by date of sale.
        $sales    = $this->monthlyCounts($allMonths, Sale::class, null, fn ($query) => $query->forBroker($broker), 'sold_at');
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
    private function monthlyCounts($months, string $model, $agentIds, ?callable $filter = null, string $dateColumn = 'created_at')
    {
        $query = $model::query()
            ->when($agentIds !== null, fn ($query) => $query->whereIn('broker_id', $agentIds))
            ->whereBetween($dateColumn, [$months->first()->copy()->startOfMonth(), $months->last()->copy()->endOfMonth()]);
        if ($filter) {
            $filter($query);
        }

        $perMonth = $query->pluck($dateColumn)->countBy(fn ($date) => Carbon::parse($date)->format('Y-m'));

        return $months->map(fn ($month) => $perMonth->get($month->format('Y-m'), 0));
    }

    public function performance(): View
    {
        // Counts for every agent at once, instead of three queries per agent.
        $agentIds = auth()->user()->agents()->select('users.id');
        $countByAgent = fn ($query) => $query->whereIn('broker_id', $agentIds)
            ->selectRaw('broker_id, count(*) as total')->groupBy('broker_id')->pluck('total', 'broker_id');
        $leads = $countByAgent(Inquiry::query());
        $viewings = $countByAgent(SiteVisit::query());

        // Sales and agent commission come from the commission records.
        $sales = Sale::forBroker(auth()->user())->toBase()
            ->selectRaw("agent_id, count(*) as sales, coalesce(sum(agent_commission), 0) as commission, coalesce(sum(case when status = 'received' then agent_commission else 0 end), 0) as received")
            ->groupBy('agent_id')->get()->keyBy('agent_id');

        $agents = auth()->user()->agents()->get()->map(function ($agent) use ($leads, $viewings, $sales) {
            $agent->leads_count = (int) $leads->get($agent->id, 0);
            $agent->viewings_count = (int) $viewings->get($agent->id, 0);
            $agent->sales_count = (int) ($sales->get($agent->id)->sales ?? 0);
            $agent->commission_expected = (float) ($sales->get($agent->id)->commission ?? 0);
            $agent->commission_paid = (float) ($sales->get($agent->id)->received ?? 0);

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
