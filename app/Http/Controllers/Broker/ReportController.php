<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $brokerId = auth()->id();

        $data = [
            'total_properties'   => Property::where('broker_id', $brokerId)->count(),
            'total_clients'      => Client::where('broker_id', $brokerId)->count(),
            'total_reservations' => Reservation::where('broker_id', $brokerId)->count(),
            'total_revenue'      => Payment::where('broker_id', $brokerId)->where('status', 'verified')->sum('amount'),
            'properties_by_status' => Property::where('broker_id', $brokerId)
                ->selectRaw("status, COUNT(*) as count")
                ->groupBy('status')
                ->pluck('count', 'status'),
            'monthly_payments'   => Payment::where('broker_id', $brokerId)
                ->where('status', 'verified')
                ->selectRaw("EXTRACT(MONTH FROM created_at) as month, SUM(amount) as total")
                ->groupByRaw("EXTRACT(MONTH FROM created_at)")
                ->pluck('total', 'month'),
        ];

        return view('pages.reports.index', compact('data'));
    }
}