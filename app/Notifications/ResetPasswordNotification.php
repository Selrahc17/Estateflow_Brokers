<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Laravel's reset email in EstateFlow's own words. The link itself is still
 * built by Laravel, so resetting works exactly as before.
 */
class ResetPasswordNotification extends ResetPassword
{
    protected function buildMailMessage($url)
    {
        $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject('Reset your EstateFlow password')
            ->greeting('Hi there!')
            ->line('We received a request to reset the password for your EstateFlow account.')
            ->action('Reset Password', $url)
            ->line("This link expires in {$minutes} minutes.")
            ->line("If you didn't ask for this, you can ignore this email. Your password won't change.");
    }
}
