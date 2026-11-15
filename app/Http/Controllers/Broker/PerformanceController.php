<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Services\AgentPerformance;
use App\Services\AgentPerformanceExport;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Agent performance and the yearly or quarterly sales ranking.
 */
class PerformanceController extends Controller
{
    public function index(Request $request): View
    {
        $report = $this->report($request);
        $ranking = $report->ranking();

        return view('pages.broker.performance', [
            'report' => $report,
            'ranking' => $ranking,
            'topAgents' => $report->topAgents($ranking),
            'monthly' => $report->monthly(),
            'years' => AgentPerformance::availableYears(auth()->user()),
        ]);
    }

    public function export(Request $request, AgentPerformanceExport $export): BinaryFileResponse
    {
        $report = $this->report($request);

        return $export->download($report, 'agent-ranking-' . str_replace(' ', '-', strtolower($report->label())) . '.xlsx');
    }

    /** The chosen year and quarter, defaulting to this year. */
    private function report(Request $request): AgentPerformance
    {
        $years = AgentPerformance::availableYears(auth()->user());
        $year = in_array($request->integer('year'), $years, true) ? $request->integer('year') : now()->year;
        $quarter = in_array($request->integer('quarter'), [1, 2, 3, 4], true) ? $request->integer('quarter') : null;

        return new AgentPerformance(auth()->user(), $year, $quarter);
    }
}
