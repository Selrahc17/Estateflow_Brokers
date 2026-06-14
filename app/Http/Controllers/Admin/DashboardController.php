<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_users'       => User::count(),
            'total_brokers'     => User::where('role', 'broker')->count(),
            'total_clients'     => Client::count(),
            'total_properties'  => Property::count(),
            'total_reservations' => Reservation::count(),
            'total_revenue'     => 0, // Placeholder, since no payment handling per scope
        ];

        $recentUsers = User::latest()->take(5)->get();
        $recentReservations = Reservation::with(['client', 'broker'])->latest()->take(5)->get();
        
        // Most active brokers (by number of properties)
        $mostActiveBrokers = User::where('role', 'broker')
            ->withCount('properties')
            ->orderBy('properties_count', 'desc')
            ->take(5)
            ->get();
            
        // Most viewed properties
        $mostViewedProperties = Property::with('broker')
            ->orderBy('view_count', 'desc')
            ->take(5)
            ->get();

        return view('pages.admin.dashboard.index', compact('stats', 'recentUsers', 'recentReservations', 'mostActiveBrokers', 'mostViewedProperties'));
    }
}