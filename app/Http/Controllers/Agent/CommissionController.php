<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use Illuminate\View\View;

/**
 * The agent's own sales. Read only: commission amounts stay private to the
 * broker, and the status shows only when the broker allowed it for that sale.
 */
class CommissionController extends Controller
{
    public function index(): View
    {
        $sales = Sale::where('agent_id', auth()->id())
            ->with(['property', 'lot'])
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(20);

        $soldCount = Sale::where('agent_id', auth()->id())->count();

        return view('pages.agent.commission.index', compact('sales', 'soldCount'));
    }
}
