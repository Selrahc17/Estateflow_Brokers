<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function index(): View
    {
        $agents = auth()->user()->agents()
            ->withCount('properties')
            ->when(request('search'), fn($q) => $q->where(function ($query) {
                $query->where('name', 'like', '%' . request('search') . '%')
                      ->orWhere('email', 'like', '%' . request('search') . '%');
            }))
            ->latest()
            ->paginate(12);

        return view('pages.broker.agents.index', compact('agents'));
    }

    public function create(): View
    {
        return view('pages.broker.agents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'avatar'   => ['nullable', 'image', 'max:2048'],
        ]);

        $avatarUrl = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'agent-avatars/' . uniqid() . '.' . $file->getClientOriginalExtension();
            Storage::disk('supabase')->put($filename, file_get_contents($file->getRealPath()), 'public');
            $avatarUrl = env('SUPABASE_URL') . '/storage/v1/object/public/properties/' . $filename;
        }

        User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'phone'       => $data['phone'] ?? null,
            'password'    => Hash::make($data['password']),
            'avatar'      => $avatarUrl,
            'role'        => 'agent',
            'broker_id'   => auth()->id(),
            'is_active'   => true,
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
            'name'   => ['required', 'string', 'max:255'],
            'email'  => ['required', 'email', Rule::unique('users', 'email')->ignore($agent->id)],
            'phone'  => ['nullable', 'string', 'max:20'],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'agent-avatars/' . uniqid() . '.' . $file->getClientOriginalExtension();
            Storage::disk('supabase')->put($filename, file_get_contents($file->getRealPath()), 'public');
            $data['avatar'] = env('SUPABASE_URL') . '/storage/v1/object/public/properties/' . $filename;
        }

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
