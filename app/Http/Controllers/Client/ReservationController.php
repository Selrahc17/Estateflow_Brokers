<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $client = auth()->user()->clientProfile;
        $reservations = $client
            ? Reservation::where('client_id', $client->id)->with('lot.property')->latest()->paginate(10)
            : collect();

        return view('pages.client.reservation.index', compact('reservations'));
    }
}