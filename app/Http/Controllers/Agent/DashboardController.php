<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SiteVisit;

class DashboardController extends Controller
{
    public function index()
    {
        $brokerId = auth()->id();

        $stats = [
            'total_properties'  => Property::where('broker_id', $brokerId)->count(),
            'total_clients'     => Client::where('broker_id', $brokerId)->count(),
            'active_reservations' => Reservation::where('broker_id', $brokerId)->where('status', 'confirmed')->count(),
            'pending_inquiries' => Inquiry::where('broker_id', $brokerId)->where('status', 'new')->count(),
            'pending_site_visits' => SiteVisit::where('broker_id', $brokerId)->where('status', 'pending')->count(),
        ];

        $recentReservations = Reservation::where('broker_id', $brokerId)
            ->with(['client', 'lot.property'])
            ->latest()
            ->take(5)
            ->get();

        $recentSiteVisits = SiteVisit::where('broker_id', $brokerId)
            ->with(['client', 'property'])
            ->latest()
            ->take(5)
            ->get();

        return view('pages.dashboard.index', compact('stats', 'recentReservations', 'recentSiteVisits'));
    }
}