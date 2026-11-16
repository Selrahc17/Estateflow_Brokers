<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Throwable;

use function Illuminate\Support\defer;

abstract class Controller
{
    /**
     * Emails an agent after the response has been sent, so the buyer does not
     * wait for the mail server. A mail problem is logged and never blocks the
     * buyer's action; the agent still has the in-app notification.
     */
    protected function emailAgent(?User $agent, Notification $notification): void
    {
        if (! $agent?->email || ! $agent->is_active) {
            return;
        }

        defer(function () use ($agent, $notification) {
            try {
                $agent->notify($notification);
            } catch (Throwable $e) {
                report($e);
            }
        });
    }
}
