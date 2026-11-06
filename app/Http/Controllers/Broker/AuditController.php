<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'actor' => ['nullable', 'integer'],
            'action' => ['nullable', 'string', 'max:50'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $broker = auth()->user();
        $agents = $broker->agents()->orderBy('name')->get(['id', 'name']);

        $logs = AuditLog::whereIn('actor_id', $agents->pluck('id')->push($broker->id))
            ->when($filters['actor'] ?? null, fn ($query, $actor) => $query->where('actor_id', $actor))
            ->when($filters['action'] ?? null, fn ($query, $action) => $query->where('action', $action))
            ->when($filters['from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->with('actor')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('pages.broker.audit.index', compact('logs', 'agents', 'broker'));
    }
}
