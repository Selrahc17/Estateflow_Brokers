<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use App\Support\Totals;
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
                'reservations as pending_res' => fn ($query) => $query->where('status', 'pending'),
                'reservations as confirmed_res' => fn ($query) => $query->whereIn('status', ['confirmed', 'completed']),
            ])
            // Each broker's totals come with the list, instead of three queries per broker.
            ->withSum(['payments as total_sales' => fn ($query) => $query->where('status', 'paid')], 'amount')
            ->orderByDesc('reservations_count')
            ->get()
            ->each(fn ($broker) => $broker->total_sales = (float) $broker->total_sales);

        $summary = Totals::of([
            'total_brokers'      => User::where('role', 'broker')->selectRaw('count(*)'),
            'total_agents'       => User::where('role', 'agent')->selectRaw('count(*)'),
            'total_clients'      => Client::selectRaw('count(*)'),
            'total_properties'   => Property::selectRaw('count(*)'),
            'total_reservations' => Reservation::selectRaw('count(*)'),
            'total_sales'        => Payment::where('status', 'paid')->selectRaw('coalesce(sum(amount), 0)'),
        ]);

        $reservationsByStatus = Reservation::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        $propertiesByStatus = Property::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')->pluck('count', 'status');

        return view('pages.admin.reports.index', compact(
            'brokers', 'summary', 'reservationsByStatus', 'propertiesByStatus'
        ));
    }
}
