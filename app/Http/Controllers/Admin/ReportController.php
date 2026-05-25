<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $data = [
            'total_users'  => User::count(),
            'by_role'      => User::selectRaw("role, COUNT(*) as count")->groupBy('role')->pluck('count', 'role'),
            'reservations_by_status' => Reservation::selectRaw("status, COUNT(*) as count")->groupBy('status')->pluck('count', 'status'),
            'total_revenue' => Payment::where('status', 'verified')->sum('amount'),
        ];

        return view('pages.admin.reports.index', compact('data'));
    }
}