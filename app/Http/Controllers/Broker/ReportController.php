<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SiteVisit;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $agentIds = auth()->user()->agents()->pluck('users.id');
        $data = [
            'total_properties' => Property::whereIn('broker_id', $agentIds)->count(),
            'total_clients' => Client::whereIn('broker_id', $agentIds)->count(),
            'total_reservations' => Reservation::whereIn('broker_id', $agentIds)->count(),
            'total_revenue' => Payment::whereIn('broker_id', $agentIds)->where('status', 'verified')->sum('amount'),
            'total_leads' => Inquiry::whereIn('broker_id', $agentIds)->count(),
            'total_viewings' => SiteVisit::whereIn('broker_id', $agentIds)->count(),
            'reservations_by_status' => Reservation::whereIn('broker_id', $agentIds)
                ->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status'),
            'monthly_revenue' => $this->monthlyRevenue($agentIds),
        ];

        $agents = auth()->user()->agents()->get()->map(function ($agent) {
            $agent->leads_count = Inquiry::where('broker_id', $agent->id)->count();
            $agent->viewings_count = SiteVisit::where('broker_id', $agent->id)->count();
            $agent->sales_count = Reservation::where('broker_id', $agent->id)
                ->whereIn('status', ['confirmed', 'completed'])->count();
            return $agent;
        })->sortByDesc('sales_count')->values();

        return view('pages.broker.reports.index', compact('data', 'agents'));
    }

    private function monthlyRevenue(Collection $agentIds): Collection
    {
        $start = now()->startOfYear();
        return collect(range(1, 12))->mapWithKeys(function (int $month) use ($agentIds, $start) {
            $amount = Payment::whereIn('broker_id', $agentIds)
                ->where('status', 'verified')
                ->whereBetween('paid_at', [$start->copy()->month($month)->startOfMonth(), $start->copy()->month($month)->endOfMonth()])
                ->sum('amount');
            return [$month => $amount];
        });
    }
}
