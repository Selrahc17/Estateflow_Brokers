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
        $brokers = User::where('role', 'agent')
            ->when(request('search'), fn($q) => $q->where(function ($query) {
                $query->where('name', 'like', '%'.request('search').'%')
                    ->orWhere('email', 'like', '%'.request('search').'%');
            }))
            ->when(request('approval_status'), fn($q) => $q->where('is_approved', request('approval_status') === 'approved'))
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

        $data['role'] = 'agent';
        $data['is_approved'] = false; // New agents need approval
        User::create($data);

        return redirect()->route('admin.brokers')->with('success', 'Broker created successfully. Pending approval.');
    }

    public function show(User $user): View
    {
        $this->ensureBroker($user);
        $user->load('clients');
        return view('pages.admin.brokers.show', compact('user'));
    }

    public function approve(User $user): RedirectResponse
    {
        $this->ensureBroker($user);
        $user->update(['is_approved' => true]);
        return back()->with('success', "Broker {$user->name} has been approved!");
    }

    public function reject(User $user): RedirectResponse
    {
        $this->ensureBroker($user);
        $user->update(['is_approved' => false]);
        return back()->with('success', "Broker {$user->name} has been rejected.");
    }

    private function ensureBroker(User $user): void
    {
        abort_unless($user->isAgent(), 404);
    }
}