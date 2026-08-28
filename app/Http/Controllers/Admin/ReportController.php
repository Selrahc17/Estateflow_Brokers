<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $brokers = User::where('role', 'broker')
            ->withCount([
                'agents',
                'properties',
                'clients',
                'reservations',
            ])
            ->orderByDesc('reservations_count')
            ->get()
            ->map(function ($broker) {
                $broker->total_sales    = Payment::where('broker_id', $broker->id)->where('status', 'paid')->sum('amount');
                $broker->pending_res    = Reservation::where('broker_id', $broker->id)->where('status', 'pending')->count();
                $broker->confirmed_res  = Reservation::where('broker_id', $broker->id)->whereIn('status', ['confirmed', 'completed'])->count();
                return $broker;
            });

        $summary = [
            'total_brokers'      => User::where('role', 'broker')->count(),
            'total_agents'       => User::where('role', 'agent')->count(),
            'total_clients'      => Client::count(),
            'total_properties'   => Property::count(),
            'total_reservations' => Reservation::count(),
            'total_sales'        => Payment::where('status', 'paid')->sum('amount'),
        ];

        $reservationsByStatus = Reservation::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $propertiesByStatus = Property::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        return view('pages.admin.reports.index', compact(
            'brokers', 'summary', 'reservationsByStatus', 'propertiesByStatus'
        ));
    }
}
