<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(): View
    {
        $agentIds = auth()->user()->agents()->pluck('users.id');
        $logs = AuditLog::whereIn('actor_id', $agentIds)
            ->with('actor')
            ->latest()
            ->take(100)
            ->get();

        return view('pages.broker.audit.index', compact('logs'));
    }
}
