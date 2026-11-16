<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/**
 * Emails the agent when a buyer asks to reserve a lot. The lot is not held
 * until the agent confirms, so a quick answer matters.
 */
class NewReservationRequest extends Notification
{
    public function __construct(private Reservation $reservation)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reservation = $this->reservation;
        $lot = $reservation->lot?->label ?? 'a lot';
        $property = $reservation->lot?->property?->name ?? 'your listing';
        $buyer = $reservation->client?->full_name ?? 'A buyer';

        return (new MailMessage)
            ->subject("Reservation request: {$lot}, {$property}")
            ->greeting('Hi ' . (Str::before(trim((string) $notifiable->name), ' ') ?: 'there') . ',')
            ->line("{$buyer} asked to reserve {$lot} at {$property}.")
            ->line('**Price:** ' . ($reservation->total_price !== null ? '₱' . number_format((float) $reservation->total_price, 0) : 'not set'))
            ->line('The lot stays available to others until you confirm the request.')
            ->action('Review Request', route('agent.reservations.show', $reservation));
    }
}
