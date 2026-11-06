<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AgentInvitation;
use Illuminate\Support\Str;

class AgentInvitationService
{
    public const VALID_DAYS = 7;

    /**
     * Creates a fresh link, which replaces any earlier one, and returns it so the
     * broker can share it directly. Only a hash of the token is stored.
     */
    public function issue(User $agent): string
    {
        $token = Str::random(64);
        $expiresAt = now()->addDays(self::VALID_DAYS);

        $agent->forceFill([
            'invitation_token' => hash('sha256', $token),
            'invitation_expires_at' => $expiresAt,
        ])->save();

        $url = route('auth.invitation.show', $token);

        rescue(fn () => $agent->notify(new AgentInvitation($agent->broker->name, $url, $expiresAt)));

        return $url;
    }

    public function find(string $token): ?User
    {
        return User::where('role', 'agent')
            ->where('is_active', true)
            ->where('invitation_token', hash('sha256', $token))
            ->where('invitation_expires_at', '>', now())
            ->first();
    }
}
