<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Sale;
use App\Models\SiteVisit;
use App\Support\Totals;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $agentIds = auth()->user()->agents()->pluck('users.id');
        $totals = Totals::of([
            'total_properties' => Property::whereIn('broker_id', $agentIds)->selectRaw('count(*)'),
            'total_clients' => Client::whereIn('broker_id', $agentIds)->selectRaw('count(*)'),
            'total_reservations' => Reservation::whereIn('broker_id', $agentIds)->selectRaw('count(*)'),
            'total_revenue' => Sale::forBroker(auth()->user())->selectRaw('coalesce(sum(selling_price), 0)'),
            'total_leads' => Inquiry::whereIn('broker_id', $agentIds)->selectRaw('count(*)'),
            'total_viewings' => SiteVisit::whereIn('broker_id', $agentIds)->selectRaw('count(*)'),
            // Agent plus broker commission from the commission records, by status.
            'commission_pending' => Sale::forBroker(auth()->user())->where('status', 'pending')->selectRaw('coalesce(sum(coalesce(agent_commission, 0) + coalesce(broker_commission, 0)), 0)'),
            'commission_transferred' => Sale::forBroker(auth()->user())->where('status', 'transferred')->selectRaw('coalesce(sum(coalesce(agent_commission, 0) + coalesce(broker_commission, 0)), 0)'),
            'commission_received' => Sale::forBroker(auth()->user())->where('status', 'received')->selectRaw('coalesce(sum(coalesce(agent_commission, 0) + coalesce(broker_commission, 0)), 0)'),
        ]);

        $data = [
            ...$totals,
            'reservations_by_status' => Reservation::whereIn('broker_id', $agentIds)
                ->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status'),
            'monthly_revenue' => $this->monthlyRevenue($agentIds),
        ];

        // Counts for every agent at once, instead of three queries per agent.
        $countByAgent = fn ($query) => $query->whereIn('broker_id', $agentIds)
            ->selectRaw('broker_id, count(*) as total')->groupBy('broker_id')->pluck('total', 'broker_id');
        $leads = $countByAgent(Inquiry::query());
        $viewings = $countByAgent(SiteVisit::query());
        $sales = Sale::forBroker(auth()->user())->selectRaw('agent_id, count(*) as total')->groupBy('agent_id')->pluck('total', 'agent_id');

        $agents = auth()->user()->agents()->get()->map(function ($agent) use ($leads, $viewings, $sales) {
            $agent->leads_count = (int) $leads->get($agent->id, 0);
            $agent->viewings_count = (int) $viewings->get($agent->id, 0);
            $agent->sales_count = (int) $sales->get($agent->id, 0);
            return $agent;
        })->sortByDesc('sales_count')->values();

        return view('pages.broker.reports.index', compact('data', 'agents'));
    }

    /**
     * Selling price of this year's sales per month. Sales are processed by the
     * developer, so the commission records are where sales are tracked.
     */
    private function monthlyRevenue(Collection $agentIds): Collection
    {
        $start = now()->startOfYear();
        $byMonth = Sale::forBroker(auth()->user())
            ->whereBetween('sold_at', [$start->toDateString(), $start->copy()->endOfYear()->toDateString()])
            ->get(['sold_at', 'selling_price'])
            ->groupBy(fn ($sale) => Carbon::parse($sale->sold_at)->month)
            ->map(fn ($sales) => $sales->sum('selling_price'));

        return collect(range(1, 12))->mapWithKeys(fn (int $month) => [$month => $byMonth->get($month, 0)]);
    }
}
