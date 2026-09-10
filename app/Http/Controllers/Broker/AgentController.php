<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Document;
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
            'document_type' => ['nullable', 'required_with:document_file', 'in:drivers_license,physical_id,postal_id,national_id,other'],
            'document_file' => ['nullable', 'required_with:document_type', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $avatarUrl = null;
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'agent-avatars/' . uniqid() . '.' . $file->getClientOriginalExtension();
            Storage::disk('supabase')->put($filename, file_get_contents($file->getRealPath()), 'public');
            $avatarUrl = env('SUPABASE_URL') . '/storage/v1/object/public/properties/' . $filename;
        }

        $agent = User::create([
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

        if ($request->hasFile('document_file')) {
            $file = $request->file('document_file');
            Document::create([
                'agent_id' => $agent->id,
                'broker_id' => auth()->id(),
                'uploaded_by' => auth()->id(),
                'name' => ucfirst(str_replace('_', ' ', $data['document_type'])),
                'type' => $data['document_type'],
                'file_path' => $file->store('agent-documents/'.$agent->id, 'local'),
                'file_size' => $file->getSize(),
                'status' => 'pending',
            ]);
        }

        return redirect()->route('broker.agents.index')->with('success', 'Agent added successfully.');
    }

    public function edit(User $agent): View
    {
        $this->ensureOwnedAgent($agent);
        $agent->load(['agentDocuments' => fn ($query) => $query->where('broker_id', auth()->id())->latest()]);
        return view('pages.broker.agents.edit', compact('agent'));
    }

    public function uploadDocument(Request $request, User $agent): RedirectResponse
    {
        $this->ensureOwnedAgent($agent);

        $data = $request->validate([
            'type' => ['required', 'in:drivers_license,physical_id,postal_id,national_id,other'],
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
        ]);

        $file = $request->file('file');
        $path = $file->store('agent-documents/'.$agent->id, 'local');

        Document::create([
            'agent_id' => $agent->id,
            'broker_id' => auth()->id(),
            'uploaded_by' => auth()->id(),
            'name' => ucfirst(str_replace('_', ' ', $data['type'])),
            'type' => $data['type'],
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'status' => 'pending',
        ]);

        return back()->with('success', 'Agent identification document uploaded successfully.');
    }

    public function downloadDocument(User $agent, Document $document)
    {
        $this->ensureOwnedAgent($agent);
        abort_unless((int) $document->agent_id === (int) $agent->id && (int) $document->broker_id === (int) auth()->id(), 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download($document->file_path, $document->name.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION));
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
