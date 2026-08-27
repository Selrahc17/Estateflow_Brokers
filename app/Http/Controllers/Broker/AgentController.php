<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function index(): View
    {
        $agents = auth()->user()->agents()->latest()->paginate(15);

        return view('pages.broker.agents.index', compact('agents'));
    }

    public function create(): View
    {
        return view('pages.broker.agents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'role' => 'agent',
            'broker_id' => auth()->id(),
            'is_active' => true,
            'is_approved' => true,
        ]);

        return redirect()->route('broker.agents.index')->with('success', 'Agent added successfully.');
    }

    public function edit(User $agent): View
    {
        $this->ensureOwnedAgent($agent);

        return view('pages.broker.agents.edit', compact('agent'));
    }

    public function update(Request $request, User $agent): RedirectResponse
    {
        $this->ensureOwnedAgent($agent);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($agent->id)],
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->validate([
                'password' => ['string', 'min:8', 'confirmed'],
            ])['password']);
        }

        $agent->update($data);

        return redirect()->route('broker.agents.index')->with('success', 'Agent updated successfully.');
    }

    public function destroy(User $agent): RedirectResponse
    {
        $this->ensureOwnedAgent($agent);
        $agent->delete();

        return redirect()->route('broker.agents.index')->with('success', 'Agent deleted successfully.');
    }

    public function toggleStatus(User $agent): RedirectResponse
    {
        $this->ensureOwnedAgent($agent);
        $agent->update(['is_active' => !$agent->is_active]);

        return back()->with('success', 'Agent ' . ($agent->is_active ? 'activated' : 'suspended') . ' successfully.');
    }

    private function ensureOwnedAgent(User $agent): void
    {
        abort_unless($agent->role === 'agent' && (int) $agent->broker_id === (int) auth()->id(), 404);
    }
}
