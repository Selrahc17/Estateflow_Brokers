<?php

namespace App\Notifications;

use Carbon\CarbonInterface;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgentInvitation extends Notification
{
    public function __construct(
        private string $brokerName,
        private string $url,
        private CarbonInterface $expiresAt,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You have been invited to join EstateFlow')
            ->greeting('Hi there!')
            ->line("{$this->brokerName} has invited you to join their team as an agent on EstateFlow.")
            ->line('Click the button below to set up your account.')
            ->action('Accept Invitation', $this->url)
            ->line('This link expires on '.$this->expiresAt->format('M j, Y').'.');
    }
}
