<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
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
        $agreements = \App\Models\CommissionAgreement::where('broker_id', auth()->id())
            ->with('payments')
            ->get();

        $totals = Totals::of([
            'total_properties' => Property::whereIn('broker_id', $agentIds)->selectRaw('count(*)'),
            'total_clients' => Client::whereIn('broker_id', $agentIds)->selectRaw('count(*)'),
            'total_reservations' => Reservation::whereIn('broker_id', $agentIds)->selectRaw('count(*)'),
            'total_revenue' => Payment::whereIn('broker_id', $agentIds)->where('status', 'verified')->selectRaw('coalesce(sum(amount), 0)'),
            'total_leads' => Inquiry::whereIn('broker_id', $agentIds)->selectRaw('count(*)'),
            'total_viewings' => SiteVisit::whereIn('broker_id', $agentIds)->selectRaw('count(*)'),
        ]);

        $data = [
            ...$totals,
            'commission_expected' => $agreements->sum(fn ($agreement) => $agreement->payments->sum('amount_due')),
            'commission_paid' => $agreements->sum(fn ($agreement) => $agreement->payments->where('payment_status', 'paid')->sum('amount_paid')),
            'commission_disputed' => $agreements->sum(fn ($agreement) => $agreement->payments->where('payment_status', 'disputed')->sum('amount_due')),
            'reservations_by_status' => Reservation::whereIn('broker_id', $agentIds)
                ->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status'),
            'monthly_revenue' => $this->monthlyRevenue($agentIds),
        ];

        // Counts for every agent at once, instead of three queries per agent.
        $countByAgent = fn ($query) => $query->whereIn('broker_id', $agentIds)
            ->selectRaw('broker_id, count(*) as total')->groupBy('broker_id')->pluck('total', 'broker_id');
        $leads = $countByAgent(Inquiry::query());
        $viewings = $countByAgent(SiteVisit::query());
        $sales = $countByAgent(Reservation::whereIn('status', ['confirmed', 'completed']));

        $agents = auth()->user()->agents()->get()->map(function ($agent) use ($leads, $viewings, $sales) {
            $agent->leads_count = (int) $leads->get($agent->id, 0);
            $agent->viewings_count = (int) $viewings->get($agent->id, 0);
            $agent->sales_count = (int) $sales->get($agent->id, 0);
            return $agent;
        })->sortByDesc('sales_count')->values();

        return view('pages.broker.reports.index', compact('data', 'agents'));
    }

    /**
     * Verified payments per month this year, from one query instead of twelve.
     */
    private function monthlyRevenue(Collection $agentIds): Collection
    {
        $start = now()->startOfYear();
        $byMonth = Payment::whereIn('broker_id', $agentIds)
            ->where('status', 'verified')
            ->whereBetween('paid_at', [$start, $start->copy()->endOfYear()])
            ->get(['paid_at', 'amount'])
            ->groupBy(fn ($payment) => Carbon::parse($payment->paid_at)->month)
            ->map(fn ($payments) => $payments->sum('amount'));

        return collect(range(1, 12))->mapWithKeys(fn (int $month) => [$month => $byMonth->get($month, 0)]);
    }
}
