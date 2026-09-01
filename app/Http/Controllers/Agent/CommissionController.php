<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\CommissionAgreement;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function index(): View
    {
        $agreements = CommissionAgreement::where('agent_id', auth()->id())
            ->with(['broker', 'property'])
            ->latest()
            ->paginate(10);

        return view('pages.agent.commission.index', compact('agreements'));
    }

    public function show(CommissionAgreement $agreement): View
    {
        abort_unless((int) $agreement->agent_id === (int) auth()->id(), 403);
        $agreement->load(['agent', 'broker', 'property', 'payments.notes']);

        return view('pages.agent.commission.show', compact('agreement'));
    }
}
