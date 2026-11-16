<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Sale;
use App\Models\SiteVisit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * One year of the agent's work: how buyers moved from inquiry to viewing to
 * reservation to sale, month by month, and which listings drew the most interest.
 * Commission amounts stay private to the broker, so only sales are shown.
 */
class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $agentId = auth()->id();
        $year = (int) $request->integer('year', now()->year);
        $year = $year >= 2000 && $year <= now()->year ? $year : now()->year;
        $start = Carbon::create($year, 1, 1)->startOfDay();
        $end = $start->copy()->endOfYear();

        // Only dates and amounts come back, and the months are counted here, so
        // this works the same on Postgres and SQLite.
        $sales = Sale::where('agent_id', $agentId)->whereBetween('sold_at', [$start, $end])->get(['sold_at', 'selling_price']);
        $inquiries = Inquiry::where('broker_id', $agentId)->whereBetween('created_at', [$start, $end])->get(['created_at', 'type', 'status']);
        $viewings = SiteVisit::where('broker_id', $agentId)->whereBetween('scheduled_at', [$start, $end])->get(['scheduled_at', 'status']);
        $reservations = Reservation::where('broker_id', $agentId)->whereBetween('created_at', [$start, $end])->get(['created_at', 'status']);

        $byMonth = fn ($rows, string $date) => $rows->countBy(fn ($row) => (int) $row->{$date}->month);
        $salesValueByMonth = $sales->groupBy(fn ($sale) => (int) $sale->sold_at->month)->map->sum(fn ($sale) => (float) $sale->selling_price);
        $monthCount = $year === now()->year ? now()->month : 12;

        $months = collect(range(1, 12))->map(fn (int $month) => [
            'label' => Carbon::create($year, $month, 1)->format('M'),
            'future' => $month > $monthCount,
            'inquiries' => (int) ($byMonth($inquiries, 'created_at')[$month] ?? 0),
            'viewings' => (int) ($byMonth($viewings->where('status', '!=', 'cancelled'), 'scheduled_at')[$month] ?? 0),
            'reservations' => (int) ($byMonth($reservations->where('status', '!=', 'cancelled'), 'created_at')[$month] ?? 0),
            'sales' => (int) ($byMonth($sales, 'sold_at')[$month] ?? 0),
            'value' => (float) ($salesValueByMonth[$month] ?? 0),
        ]);

        $funnel = [
            'Inquiries' => $inquiries->count(),
            'Viewings' => $viewings->whereIn('status', ['confirmed', 'completed'])->count(),
            'Reservations' => $reservations->whereIn('status', ['pending', 'confirmed', 'completed'])->count(),
            'Sales' => $sales->count(),
        ];

        $stats = [
            'sales' => $sales->count(),
            'value' => (float) $sales->sum('selling_price'),
            'inquiries' => $inquiries->count(),
            'unanswered' => $inquiries->where('status', 'new')->count(),
            'viewings_done' => $viewings->where('status', 'completed')->count(),
            'conversion' => $inquiries->count() ? round($sales->count() / $inquiries->count() * 100, 1) : null,
        ];

        $topListings = Property::where('broker_id', $agentId)
            ->withCount([
                'inquiries' => fn ($query) => $query->whereBetween('created_at', [$start, $end]),
                'siteVisits as viewings_count' => fn ($query) => $query->whereBetween('scheduled_at', [$start, $end])->where('status', '!=', 'cancelled'),
            ])
            ->orderByDesc('inquiries_count')
            ->orderBy('name')
            ->take(5)
            ->get(['id', 'name', 'status', 'city'])
            ->filter(fn ($property) => $property->inquiries_count > 0 || $property->viewings_count > 0)
            ->values();

        $firstYear = collect([
            Sale::where('agent_id', $agentId)->min('sold_at'),
            Inquiry::where('broker_id', $agentId)->min('created_at'),
        ])->filter()->map(fn ($date) => Carbon::parse($date)->year)->min() ?? now()->year;
        $years = range(now()->year, min($firstYear, $year));

        return view('pages.reports.index', compact('year', 'years', 'months', 'funnel', 'stats', 'topListings'));
    }
}
