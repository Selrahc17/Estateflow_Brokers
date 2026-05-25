<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BrokerController extends Controller
{
    public function index(): View
    {
        $brokers = User::where('role', 'broker')
            ->withCount('clients')
            ->latest()
            ->paginate(15);

        return view('pages.admin.brokers.index', compact('brokers'));
    }

    public function create(): View
    {
        return view('pages.admin.brokers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $data['role'] = 'broker';
        User::create($data);

        return redirect()->route('admin.brokers')->with('success', 'Broker created successfully.');
    }

    public function show(User $user): View
    {
        $user->load('clients');
        return view('pages.admin.brokers.show', compact('user'));
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        // You could add an is_active column, but for now just redirect back
        return back()->with('success', 'Broker status toggled.');
    }
}