<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Reservation;

class DashboardController extends Controller
{
    public function index()
    {
        $brokerId = auth()->id();

        $stats = [
            'total_properties'  => Property::where('broker_id', $brokerId)->count(),
            'total_clients'     => Client::where('broker_id', $brokerId)->count(),
            'active_reservations' => Reservation::where('broker_id', $brokerId)->where('status', 'confirmed')->count(),
            'pending_payments'  => Payment::where('broker_id', $brokerId)->where('status', 'pending')->count(),
        ];

        $recentReservations = Reservation::where('broker_id', $brokerId)
            ->with(['client', 'lot.property'])
            ->latest()
            ->take(5)
            ->get();

        $recentPayments = Payment::where('broker_id', $brokerId)
            ->with(['client', 'reservation'])
            ->latest()
            ->take(5)
            ->get();

        return view('pages.dashboard.index', compact('stats', 'recentReservations', 'recentPayments'));
    }
}