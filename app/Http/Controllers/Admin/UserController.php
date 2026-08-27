<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::when(request('search'), fn($q) => $q->where(function ($query) {
                $query->where('name', 'like', '%'.request('search').'%')
                    ->orWhere('email', 'like', '%'.request('search').'%');
            }))
            ->when(request('role'), fn($q) => $q->where('role', request('role')))
            ->latest()
            ->paginate(15);
        return view('pages.admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $brokers = User::where('role', 'broker')->orderBy('name')->get();
        return view('pages.admin.users.create', compact('brokers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:admin,broker,agent,client',
            'broker_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'broker')],
        ]);

        $data['broker_id'] = $data['role'] === 'agent' ? ($data['broker_id'] ?? null) : null;

        User::create($data);

        return redirect()->route('admin.users')->with('success', 'User created successfully.');
    }

    public function edit(User $user): View
    {
        $brokers = User::where('role', 'broker')->whereKeyNot($user->id)->orderBy('name')->get();
        return view('pages.admin.users.edit', compact('user', 'brokers'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role'  => 'required|in:admin,broker,agent,client',
            'broker_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'broker')],
        ]);

        $data['broker_id'] = $data['role'] === 'agent' ? ($data['broker_id'] ?? null) : null;

        if ($request->filled('password')) {
            $request->validate(['password' => 'string|min:8|confirmed']);
            $data['password'] = $request->password;
        }

        $user->update($data);

        return redirect()->route('admin.users')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }
        $user->delete();
        return redirect()->route('admin.users')->with('success', 'User deleted successfully.');
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot suspend your own account.');
        }
        $user->update(['is_active' => !$user->is_active]);
        return redirect()->route('admin.users')->with('success', "User {$user->name} has been " . ($user->is_active ? 'reactivated' : 'suspended') . ".");
    }
}