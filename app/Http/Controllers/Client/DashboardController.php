<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Reservation;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $client = auth()->user()->clientProfile;

        $stats = [
            'active_reservation' => $client ? Reservation::where('client_id', $client->id)->where('status', 'confirmed')->count() : 0,
            'pending_payments'   => $client ? $client->reservations()->withCount(['payments as pending_payments' => fn($q) => $q->where('status', 'pending')])->get()->sum('pending_payments') : 0,
        ];

        $recentReservations = $client ? Reservation::where('client_id', $client->id)->with('lot.property')->latest()->take(3)->get() : collect();
        $notifications = AppNotification::where('user_id', auth()->id())->latest()->take(5)->get();

        return view('pages.client.dashboard.index', compact('client', 'stats', 'recentReservations', 'notifications'));
    }
}