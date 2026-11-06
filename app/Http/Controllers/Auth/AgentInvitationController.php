<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Services\AgentInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AgentInvitationController extends Controller
{
    public function __construct(private AgentInvitationService $invitations)
    {
    }

    public function show(string $token): Response
    {
        $agent = $this->invitations->find($token);

        return response()->view('pages.auth.invitation', compact('agent', 'token'), $agent ? 200 : 410);
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $agent = $this->invitations->find($token);

        if (! $agent) {
            return redirect()->route('auth.invitation.show', $token);
        }

        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
            'license_number' => ['required', 'string', 'max:100', Rule::unique('users', 'license_number')->ignore($agent->id)],
            'prc_id_expires_at' => ['required', 'date', 'after:today'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'privacy_consent' => ['accepted'],
        ], [
            'license_number.unique' => 'This accreditation number is already registered.',
            'prc_id_expires_at.after' => 'Your accreditation has expired. Please renew it first.',
            'password.min' => 'Your password must be at least 8 characters.',
            'password.confirmed' => 'The passwords do not match.',
            'privacy_consent.accepted' => 'Please agree to the Privacy Policy to continue.',
        ], [
            'license_number' => 'PRC accreditation number',
            'prc_id_expires_at' => 'accreditation expiry date',
        ]);

        $agent->forceFill([
            'phone' => $data['phone'] ?? $agent->phone,
            'license_number' => $data['license_number'],
            'prc_id_expires_at' => $data['prc_id_expires_at'],
            'password' => $data['password'],
            'is_approved' => true,
            'email_verified_at' => now(),
            'privacy_consented_at' => now(),
            'invitation_token' => null,
            'invitation_expires_at' => null,
        ])->save();

        AppNotification::create([
            'user_id' => $agent->broker_id,
            'type' => 'agent_invitation',
            'title' => 'Agent joined your team',
            'message' => "{$agent->name} accepted your invitation.",
            'link' => route('broker.agents.edit', $agent),
            'is_read' => false,
        ]);

        Auth::login($agent);
        $request->session()->regenerate();

        return redirect()->route('agent.dashboard');
    }
}
