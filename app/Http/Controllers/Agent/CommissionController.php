<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The agent's own sales. Read only: commission amounts stay private to the
 * broker, and the status shows only when the broker allowed it for that sale.
 */
class CommissionController extends Controller
{
    public function index(Request $request): View
    {
        $mine = fn () => Sale::where('agent_id', auth()->id());

        $years = $mine()->pluck('sold_at')->map(fn ($date) => (int) $date->year)->unique()->sortDesc()->values();
        $year = $years->contains($request->integer('year')) ? $request->integer('year') : null;

        $inPeriod = fn () => $mine()->when($year, fn ($query) => $query->whereYear('sold_at', $year));
        $stats = [
            'sales' => $inPeriod()->count(),
            'value' => (float) $inPeriod()->sum('selling_price'),
            'this_year' => $mine()->whereYear('sold_at', now()->year)->count(),
            // Only statuses the broker chose to share are counted.
            'received' => $inPeriod()->where('show_status_to_agent', true)->where('status', 'received')->count(),
        ];

        $sales = $inPeriod()
            ->with(['property', 'lot'])
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('pages.agent.commission.index', compact('sales', 'stats', 'years', 'year'));
    }
}
