<?php

namespace App\Notifications;

use App\Models\Inquiry;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Emails the listing's agent as soon as a buyer sends an inquiry or asks to
 * view a property, so they can reply while the buyer is still interested.
 */
class NewInquiry extends Notification
{
    public function __construct(private Inquiry $inquiry)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $inquiry = $this->inquiry;
        $property = $inquiry->property?->name ?? 'your listing';
        $buyer = $inquiry->contact_label;
        $when = $inquiry->preferred_viewing_at?->format('l, F j \a\t g:i A');

        $mail = (new MailMessage)
            ->subject($inquiry->isViewingRequest() ? "Viewing request: {$property}" : "New inquiry: {$property}")
            ->greeting('Hi ' . (Str::before(trim((string) $notifiable->name), ' ') ?: 'there') . ',')
            ->line($inquiry->isViewingRequest()
                ? "{$buyer} would like to view {$property}" . ($when ? " on {$when}." : '.')
                : "{$buyer} sent an inquiry about {$property}.")
            ->line("**Email:** {$inquiry->email}");

        if ($inquiry->phone) {
            $mail->line("**Phone:** {$inquiry->phone}");
        }
        if ($inquiry->facebook) {
            $mail->line("**Facebook:** {$inquiry->facebook}");
        }

        return $mail
            ->line('**Message:** ' . Str::limit((string) $inquiry->message, 600))
            ->action($inquiry->isViewingRequest() ? 'Open Viewing Request' : 'Open Inquiry', route('agent.inquiries.show', $inquiry))
            ->line($inquiry->isViewingRequest()
                ? 'Confirm the schedule with the buyer, then book the site visit in EstateFlow.'
                : 'Buyers who get a quick reply are more likely to keep going.');
    }
}
