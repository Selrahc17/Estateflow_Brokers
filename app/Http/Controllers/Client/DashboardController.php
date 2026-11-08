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
        $profiles = auth()->user()->clientProfiles()->with('broker:id,name')->get();
        $profileIds = $profiles->pluck('id');

        $stats = [
            'active_reservation' => Reservation::whereIn('client_id', $profileIds)->where('status', 'confirmed')->count(),
        ];

        $recentReservations = Reservation::whereIn('client_id', $profileIds)->with('lot.property')->latest()->take(3)->get();
        $notifications = AppNotification::where('user_id', auth()->id())->latest()->take(5)->get();

        return view('pages.client.dashboard.index', compact('profiles', 'stats', 'recentReservations', 'notifications'));
    }
}