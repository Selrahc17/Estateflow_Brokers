<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Lot;
use App\Models\Property;
use App\Models\User;
use App\Notifications\NewInquiry;
use App\Notifications\NewReservationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * Agents get an email for every new inquiry, viewing request and
 * reservation request, not only the bell notification.
 */
class AgentEmailTest extends TestCase
{
    use RefreshDatabase;

    private User $agent;

    private Property $property;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->agent = User::factory()->create(['name' => 'Ryan Yasona', 'email' => 'ryan@example.com', 'role' => 'agent', 'is_active' => true, 'is_approved' => true]);
        $this->property = Property::create(['broker_id' => $this->agent->id, 'name' => 'Sabang Subdivision Lots', 'slug' => 'sabang', 'type' => 'Subdivision Lot Only', 'status' => 'available']);
    }

    private function inquire(array $overrides = [])
    {
        return $this->post(route('client.property.inquire', $this->property), array_merge([
            'name' => 'Jane Buyer', 'email' => 'jane@example.com', 'phone' => '09171234567', 'facebook' => 'Jane B',
            'message' => 'Is Blk 2 Lot 45 still available? Thank you.',
        ], $overrides));
    }

    public function test_an_inquiry_emails_the_agent_with_the_buyers_details(): void
    {
        $this->inquire()->assertSessionHas('success');

        Notification::assertSentTo($this->agent, NewInquiry::class, function (NewInquiry $notification) {
            $mail = $notification->toMail($this->agent);
            $text = implode("\n", $mail->introLines) . "\n" . implode("\n", $mail->outroLines);

            return $mail->subject === 'New inquiry: Sabang Subdivision Lots'
                && $mail->greeting === 'Hi Ryan,'
                && str_contains($text, 'Jane Buyer sent an inquiry about Sabang Subdivision Lots.')
                && str_contains($text, '**Phone:** 09171234567')
                && str_contains($text, '**Facebook:** Jane B')
                && str_contains($text, 'Is Blk 2 Lot 45 still available?')
                && $mail->actionUrl === route('agent.inquiries.show', Inquiry::sole());
        });
    }

    public function test_a_viewing_request_email_shows_the_preferred_schedule(): void
    {
        $day = now()->addDays(3);
        $this->inquire(['type' => 'viewing', 'preferred_date' => $day->toDateString(), 'preferred_time' => '14:30']);

        Notification::assertSentTo($this->agent, NewInquiry::class, function (NewInquiry $notification) use ($day) {
            $mail = $notification->toMail($this->agent);

            return $mail->subject === 'Viewing request: Sabang Subdivision Lots'
                && str_contains(implode("\n", $mail->introLines), "would like to view Sabang Subdivision Lots on {$day->format('l, F j')} at 2:30 PM.")
                && $mail->actionText === 'Open Viewing Request';
        });
    }

    public function test_a_reservation_request_emails_the_agent(): void
    {
        $lot = Lot::create(['property_id' => $this->property->id, 'lot_number' => 'Blk 2 Lot 45', 'price' => 360000, 'status' => 'available']);
        $buyer = User::factory()->create(['name' => 'Ana Reyes', 'role' => 'client', 'is_active' => true, 'is_approved' => true]);
        Client::create(['user_id' => $buyer->id, 'broker_id' => $this->agent->id, 'first_name' => 'Ana', 'last_name' => 'Reyes', 'email' => $buyer->email, 'password' => 'x', 'status' => 'active']);

        $this->actingAs($buyer)->post(route('client.account.reservation.store', $lot))->assertSessionHas('success');

        Notification::assertSentTo($this->agent, NewReservationRequest::class, function (NewReservationRequest $notification) {
            $mail = $notification->toMail($this->agent);

            return $mail->subject === 'Reservation request: Blk 2 Lot 45, Sabang Subdivision Lots'
                && str_contains(implode("\n", $mail->introLines), 'Ana Reyes asked to reserve Blk 2 Lot 45 at Sabang Subdivision Lots.')
                && str_contains(implode("\n", $mail->introLines), '₱360,000');
        });
    }

    public function test_a_deactivated_agent_is_not_emailed_but_the_inquiry_still_goes_through(): void
    {
        $this->agent->update(['is_active' => false]);

        $this->inquire()->assertSessionHas('success');

        $this->assertSame(1, Inquiry::count());
        Notification::assertNothingSent();
    }
}
