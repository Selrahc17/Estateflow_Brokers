<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Services\AgentInvitationService;
use App\Services\BrokerComplianceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgentController extends Controller
{
    public function index(BrokerComplianceService $compliance): View
    {
        $broker = auth()->user();
        $team = $broker->agents()->get();
        $status = in_array(request('status'), ['active', 'invited', 'suspended', 'renewal'], true) ? request('status') : null;

        $summary = [
            'active' => $team->filter(fn (User $agent) => $agent->is_approved && $agent->is_active)->count(),
            'invited' => $team->reject(fn (User $agent) => $agent->is_approved)->count(),
            'suspended' => $team->filter(fn (User $agent) => $agent->is_approved && ! $agent->is_active)->count(),
            'renewal' => $team->filter(fn (User $agent) => $agent->is_approved
                && $compliance->renewalState($agent->prc_id_expires_at) !== 'valid')->count(),
        ];

        $agents = $broker->agents()
            ->withCount(['properties', 'clients'])
            ->when(request('search'), fn ($q) => $q->where(function ($query) {
                $query->where('name', 'like', '%' . request('search') . '%')
                      ->orWhere('email', 'like', '%' . request('search') . '%');
            }))
            ->when($status === 'active', fn ($q) => $q->where('is_approved', true)->where('is_active', true))
            ->when($status === 'invited', fn ($q) => $q->where('is_approved', false))
            ->when($status === 'suspended', fn ($q) => $q->where('is_approved', true)->where('is_active', false))
            ->when($status === 'renewal', fn ($q) => $q->where('is_approved', true)->where(fn ($query) => $query
                ->whereNull('prc_id_expires_at')
                ->orWhereDate('prc_id_expires_at', '<=', today()->addDays(BrokerComplianceService::RENEWAL_WARNING_DAYS))))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $agentCount = $team->count();
        $agentLimit = config('brokerage.max_agents_per_broker');
        $atLimit = $agentCount >= $agentLimit;

        return view('pages.broker.agents.index', compact('agents', 'summary', 'status', 'agentCount', 'agentLimit', 'atLimit'));
    }

    public function store(Request $request, AgentInvitationService $invitations): RedirectResponse
    {
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
        ], ['email.unique' => 'An account with this email already exists.']);

        $limit = config('brokerage.max_agents_per_broker');

        if (auth()->user()->agents()->count() >= $limit) {
            return back()->withInput()->withErrors(['email' => "You have reached the limit of {$limit} agents per broker."]);
        }

        // Together, so a failed invitation cannot leave an account with no link.
        [$agent, $link] = DB::transaction(function () use ($data, $invitations) {
            $agent = User::create([
                'name'        => $data['name'],
                'email'       => $data['email'],
                'phone'       => $data['phone'] ?? null,
                'password'    => Str::random(40),
                'role'        => 'agent',
                'broker_id'   => auth()->id(),
                'is_active'   => true,
                'is_approved' => false,
            ]);

            return [$agent, $invitations->issue($agent)];
        });

        return redirect()->route('broker.agents.index')
            ->with('success', "Invitation created for {$agent->name}.")
            ->with('invite_link', $link);
    }

    public function resendInvitation(User $agent, AgentInvitationService $invitations): RedirectResponse
    {
        $this->ensureOwnedAgent($agent);
        abort_unless($agent->isInvited(), 404);

        return redirect()->route('broker.agents.index')
            ->with('success', "New invitation created for {$agent->name}. The previous link no longer works.")
            ->with('invite_link', $invitations->issue($agent));
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
            'license_number' => ['nullable', 'string', 'max:100', Rule::unique('users', 'license_number')->ignore($agent->id)],
            'prc_id_expires_at' => ['nullable', 'date'],
        ], [], [
            'license_number' => 'PRC accreditation number',
            'prc_id_expires_at' => 'accreditation expiry date',
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
