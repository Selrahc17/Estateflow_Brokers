<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function index(): View
    {
        $data = [
            'recent_users'        => User::latest()->take(20)->get(),
            'recent_reservations' => Reservation::with('broker')->latest()->take(20)->get(),
        ];

        return view('pages.admin.audit.index', compact('data'));
    }
}