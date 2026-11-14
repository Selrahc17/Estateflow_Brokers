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

        $stats = \App\Support\Totals::of([
            'total_properties'  => Property::where('broker_id', $brokerId)->selectRaw('count(*)'),
            'total_clients'     => Client::where('broker_id', $brokerId)->selectRaw('count(*)'),
            'active_reservations' => Reservation::where('broker_id', $brokerId)->where('status', 'confirmed')->selectRaw('count(*)'),
            'pending_inquiries' => Inquiry::where('broker_id', $brokerId)->where('status', 'new')->selectRaw('count(*)'),
            'pending_site_visits' => SiteVisit::where('broker_id', $brokerId)->where('status', 'pending')->selectRaw('count(*)'),
        ]);

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