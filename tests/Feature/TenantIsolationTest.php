<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\ChatMessage;
use App\Models\Client;
use App\Models\CommissionAgreement;
use App\Models\CommissionPayment;
use App\Models\Document;
use App\Models\Inquiry;
use App\Models\Lot;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * EstateFlow is shared by unrelated Brokers, so each team's records must stay
 * out of reach of every other team.
 */
class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    private array $mine;

    private array $theirs;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        Storage::fake('supabase');

        $this->mine = $this->team('Mine');
        $this->theirs = $this->team('Theirs');
    }

    private function team(string $tag): array
    {
        $broker = User::factory()->create(['name' => "Broker{$tag}", 'role' => 'broker', 'is_active' => true, 'is_approved' => true]);

        return ['broker' => $broker] + $this->agentRecords($broker, $tag);
    }

    private function agentRecords(User $broker, string $tag): array
    {
        $agent = User::factory()->create(['name' => "Agent{$tag}", 'role' => 'agent', 'broker_id' => $broker->id, 'is_active' => true, 'is_approved' => true]);
        $clientUser = User::factory()->create(['name' => "ClientUser{$tag}", 'role' => 'client', 'is_active' => true, 'is_approved' => true]);
        $client = Client::create(['user_id' => $clientUser->id, 'broker_id' => $agent->id, 'first_name' => "Client{$tag}", 'last_name' => 'Person', 'email' => "client{$tag}@example.com", 'password' => 'secret', 'status' => 'active', 'qualification_status' => 'approved']);
        $property = Property::create(['broker_id' => $agent->id, 'name' => "Property{$tag}", 'slug' => "property-{$tag}", 'address' => 'Address', 'type' => 'House and Lot', 'status' => 'hidden', 'price' => 1000000]);
        $lot = Lot::create(['property_id' => $property->id, 'lot_number' => "Lot{$tag}", 'status' => 'available']);
        $reservation = Reservation::create(['client_id' => $client->id, 'broker_id' => $agent->id, 'lot_id' => $lot->id, 'reservation_code' => "RES-{$tag}", 'status' => 'pending', 'total_price' => 1000000]);
        $inquiry = Inquiry::create(['user_id' => $clientUser->id, 'broker_id' => $agent->id, 'property_id' => $property->id, 'message' => "Inquiry{$tag}", 'email' => "inquiry{$tag}@example.com", 'status' => 'new']);
        $visit = SiteVisit::create(['client_id' => $client->id, 'property_id' => $property->id, 'broker_id' => $agent->id, 'scheduled_at' => now()->addDay(), 'status' => 'pending']);
        $agreement = CommissionAgreement::create(['broker_id' => $broker->id, 'agent_id' => $agent->id, 'property_id' => $property->id, 'commission_rate' => 5, 'broker_share' => 40, 'agent_share' => 60, 'payment_schedule' => 'monthly', 'payment_day' => 15, 'start_date' => now()->startOfYear()->toDateString(), 'status' => 'active']);
        $payment = $agreement->payments()->create(['due_date' => now()->toDateString(), 'amount_due' => 200000, 'agent_amount' => 120000, 'broker_amount' => 80000, 'amount_paid' => 200000, 'payment_status' => 'paid', 'proof_path' => "commission-proof/{$tag}.pdf"]);
        Storage::disk('local')->put("commission-proof/{$tag}.pdf", 'receipt');
        Storage::disk('local')->put("documents/{$tag}.pdf", 'id');
        $clientDocument = Document::create(['client_id' => $client->id, 'name' => "ClientDoc{$tag}", 'type' => 'id', 'file_path' => "documents/{$tag}.pdf", 'status' => 'pending']);
        $agentDocument = Document::create(['agent_id' => $agent->id, 'broker_id' => $broker->id, 'name' => "AgentDoc{$tag}", 'type' => 'national_id', 'file_path' => "documents/{$tag}.pdf", 'status' => 'pending']);
        $brokerDocument = Document::create(['broker_id' => $broker->id, 'uploaded_by' => $broker->id, 'name' => "BrokerDoc{$tag}", 'type' => 'bond', 'file_path' => "documents/{$tag}.pdf", 'status' => 'pending']);
        $brokerNotification = AppNotification::create(['user_id' => $broker->id, 'type' => 'info', 'title' => "Notice{$tag}", 'is_read' => false]);
        $agentNotification = AppNotification::create(['user_id' => $agent->id, 'type' => 'info', 'title' => "NoticeAgent{$tag}", 'is_read' => false]);

        return compact('agent', 'clientUser', 'client', 'property', 'lot', 'reservation', 'inquiry', 'visit', 'agreement', 'payment', 'clientDocument', 'agentDocument', 'brokerDocument', 'brokerNotification', 'agentNotification');
    }

    private function assertBlocked(User $actor, string $method, string $routeName, mixed $parameters, array $data = []): void
    {
        $response = $this->actingAs($actor)->call($method, route($routeName, $parameters), $data);

        $this->assertContains($response->getStatusCode(), [403, 404], "{$actor->role} reached {$method} {$routeName}");
    }

    private function agentAttempts(array $target): array
    {
        return [
            ['GET', 'agent.clients.show', $target['client']],
            ['GET', 'agent.clients.edit', $target['client']],
            ['PUT', 'agent.clients.update', $target['client'], ['first_name' => 'Changed', 'last_name' => 'Name', 'email' => 'changed@example.com']],
            ['PATCH', 'agent.clients.qualification-status', $target['client'], ['qualification_status' => 'rejected']],
            ['GET', 'agent.commission.show', $target['agreement']],
            ['POST', 'agent.commission.update-payment', $target['agreement'], ['payment_id' => $target['payment']->id, 'payment_status' => 'disputed']],
            ['GET', 'agent.documents.download', $target['clientDocument']],
            ['POST', 'agent.documents.check', $target['clientDocument']],
            ['POST', 'agent.documents.verify', $target['clientDocument']],
            ['POST', 'agent.documents.reject', $target['clientDocument'], ['notes' => 'No']],
            ['GET', 'agent.inquiries.show', $target['inquiry']],
            ['PATCH', 'agent.inquiries.status', $target['inquiry'], ['status' => 'closed']],
            ['GET', 'agent.lots.edit', $target['lot']],
            ['PUT', 'agent.lots.update', $target['lot'], ['property_id' => $target['lot']->property_id, 'lot_number' => 'Changed', 'status' => 'sold']],
            ['DELETE', 'agent.lots.destroy', $target['lot']],
            ['POST', 'agent.notifications.read', $target['agentNotification']],
            ['GET', 'agent.properties.show', $target['property']],
            ['GET', 'agent.properties.edit', $target['property']],
            ['PUT', 'agent.properties.update', $target['property'], ['name' => 'Changed', 'address' => 'Changed', 'type' => 'House and Lot', 'status' => 'hidden']],
            ['DELETE', 'agent.properties.destroy', $target['property']],
            ['GET', 'agent.reservations.show', $target['reservation']],
            ['PATCH', 'agent.reservations.status', $target['reservation'], ['status' => 'cancelled']],
            ['GET', 'agent.site-visits.show', $target['visit']],
            ['GET', 'agent.site-visits.edit', $target['visit']],
            ['PATCH', 'agent.site-visits.status', $target['visit'], ['status' => 'cancelled']],
        ];
    }

    public function test_a_broker_cannot_reach_another_brokers_records(): void
    {
        $broker = $this->mine['broker'];
        $target = $this->theirs;

        $attempts = [
            ['GET', 'broker.agents.edit', $target['agent']],
            ['PUT', 'broker.agents.update', $target['agent'], ['name' => 'Changed', 'email' => 'changed@example.com']],
            ['PATCH', 'broker.agents.toggle-status', $target['agent']],
            ['POST', 'broker.agents.invitation', $target['agent']],
            ['DELETE', 'broker.agents.destroy', $target['agent']],
            ['GET', 'broker.agents.documents.download', [$target['agent'], $target['agentDocument']]],
            ['GET', 'broker.agents.documents.download', [$this->mine['agent'], $target['agentDocument']]],
            ['GET', 'broker.commissions.show', $target['agreement']],
            ['GET', 'broker.commissions.pay', $target['agreement']],
            ['POST', 'broker.commissions.update-payment', $target['agreement'], ['payment_id' => $target['payment']->id, 'amount_paid' => 200000, 'payment_method' => 'cash']],
            ['POST', 'broker.notifications.read', $target['brokerNotification']],
            ['PATCH', 'broker.property-lists.terms', $target['property'], ['agent_commission' => 50]],
            ['GET', 'broker.settings.documents.download', $target['brokerDocument']],
            ['GET', 'broker.team.clients.show', $target['client']],
        ];

        foreach ($attempts as $attempt) {
            $this->assertBlocked($broker, ...$attempt);
        }

        $this->assertDatabaseHas('users', ['id' => $target['agent']->id, 'name' => 'AgentTheirs', 'is_active' => true]);
        $this->assertNull($target['property']->fresh()->agent_commission);
    }

    public function test_an_agent_cannot_reach_another_brokers_records(): void
    {
        foreach ($this->agentAttempts($this->theirs) as $attempt) {
            $this->assertBlocked($this->mine['agent'], ...$attempt);
        }

        $this->assertModelExists($this->theirs['property']);
        $this->assertSame('pending', $this->theirs['reservation']->fresh()->status);
    }

    public function test_agents_on_the_same_team_keep_their_clients_and_listings_separate(): void
    {
        $colleague = $this->agentRecords($this->mine['broker'], 'Colleague');

        foreach ($this->agentAttempts($colleague) as $attempt) {
            $this->assertBlocked($this->mine['agent'], ...$attempt);
        }
    }

    public function test_a_client_cannot_reach_another_clients_records(): void
    {
        $client = $this->mine['clientUser'];
        $target = $this->theirs;

        $this->assertBlocked($client, 'GET', 'client.account.documents.download', $target['clientDocument']);
        $this->assertBlocked($client, 'GET', 'client.account.inquiries.show', $target['inquiry']);
        $this->assertBlocked($client, 'PATCH', 'client.account.site-visits.reschedule', $target['visit'], ['scheduled_at' => now()->addDays(3)->toDateTimeString()]);
        $this->assertBlocked($client, 'PATCH', 'client.account.site-visits.cancel', $target['visit']);

        $this->assertSame('pending', $target['visit']->fresh()->status);
    }

    public function test_forms_reject_another_teams_ids(): void
    {
        $agent = $this->mine['agent'];
        $broker = $this->mine['broker'];
        $target = $this->theirs;
        $commission = ['commission_rate' => 5, 'agent_share' => 60, 'broker_share' => 40, 'payment_schedule' => 'monthly', 'payment_day' => 15, 'start_date' => now()->toDateString()];
        $reservation = ['total_price' => 1, 'payment_schedule' => 'monthly', 'payment_terms_months' => 12];

        $this->actingAs($agent)->post(route('agent.reservations.store'), $reservation + ['client_id' => $this->mine['client']->id, 'lot_id' => $target['lot']->id])->assertSessionHasErrors('lot_id');
        $this->actingAs($agent)->post(route('agent.reservations.store'), $reservation + ['client_id' => $target['client']->id, 'lot_id' => $this->mine['lot']->id])->assertSessionHasErrors('client_id');
        $this->actingAs($agent)->post(route('agent.lots.store'), ['property_id' => $target['property']->id, 'lot_number' => 'Added', 'status' => 'available'])->assertSessionHasErrors('property_id');
        $this->actingAs($agent)->post(route('agent.site-visits.store'), ['client_id' => $target['client']->id, 'property_id' => $this->mine['property']->id, 'scheduled_at' => now()->addDay()->toDateTimeString(), 'status' => 'pending'])->assertSessionHasErrors('client_id');
        $this->actingAs($broker)->post(route('broker.notifications.send'), ['user_id' => $target['agent']->id, 'title' => 'Hello'])->assertSessionHasErrors('user_id');
        $this->actingAs($broker)->post(route('broker.commissions.store'), $commission + ['agent_id' => $target['agent']->id, 'property_id' => $this->mine['property']->id])->assertSessionHasErrors('agent_id');
        $this->actingAs($broker)->post(route('broker.commissions.store'), $commission + ['agent_id' => $this->mine['agent']->id, 'property_id' => $target['property']->id])->assertSessionHasErrors('property_id');

        $this->assertSame(1, Reservation::where('lot_id', $target['lot']->id)->count());
        $this->assertSame(2, CommissionAgreement::count());
    }

    public function test_list_pages_never_show_another_teams_data(): void
    {
        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();
            $actor = match (true) {
                str_starts_with($name, 'broker.') => $this->mine['broker'],
                str_starts_with($name, 'agent.') => $this->mine['agent'],
                default => null,
            };

            // Monthly report queries use Postgres date functions the test database lacks.
            if (! $actor || $name === 'agent.reports.index' || str_contains($route->uri(), '{') || ! in_array('GET', $route->methods(), true)) {
                continue;
            }

            $this->actingAs($actor)->get(route($name))->assertDontSee('Theirs');
        }
    }

    public function test_messages_reach_your_own_team_and_stop_at_other_teams(): void
    {
        $broker = $this->mine['broker'];
        $agent = $this->mine['agent'];

        $this->actingAs($broker)
            ->post(route('broker.messages.store'), ['receiver_id' => $agent->id, 'message' => 'Hello team'])
            ->assertRedirect();
        $this->assertDatabaseHas('chat_messages', ['sender_id' => $broker->id, 'receiver_id' => $agent->id, 'message' => 'Hello team']);
        $this->actingAs($agent)->get(route('agent.messages.index', ['contact' => $broker->id]))->assertSee('Hello team');

        $this->actingAs($broker)->post(route('broker.messages.store'), ['receiver_id' => $this->theirs['agent']->id, 'message' => 'No'])->assertForbidden();
        $this->actingAs($agent)->post(route('agent.messages.store'), ['receiver_id' => $this->theirs['broker']->id, 'message' => 'No'])->assertForbidden();
        $this->assertDatabaseMissing('chat_messages', ['message' => 'No']);
    }

    public function test_chat_photos_are_private_to_the_two_people_in_the_conversation(): void
    {
        $broker = $this->mine['broker'];
        $agent = $this->mine['agent'];

        $this->actingAs($broker)->post(route('broker.messages.store'), [
            'receiver_id' => $agent->id,
            'photo' => UploadedFile::fake()->create('receipt.jpg', 10, 'image/jpeg'),
        ])->assertRedirect();

        $message = ChatMessage::latest('id')->first();
        Storage::disk('local')->assertExists($message->attachment);
        Storage::disk('public')->assertMissing($message->attachment);

        $url = route('files.chat-photo', $message);
        $this->actingAs($broker)->get($url)->assertOk();
        $this->actingAs($agent)->get($url)->assertOk();
        $this->actingAs($this->theirs['broker'])->get($url)->assertNotFound();
        $this->actingAs($this->mine['clientUser'])->get($url)->assertNotFound();

        auth()->logout();
        $this->get($url)->assertRedirect(route('auth.login'));
    }

    public function test_commission_proofs_open_only_for_the_people_on_the_agreement(): void
    {
        $url = route('files.commission-proof', $this->mine['payment']);
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'is_approved' => true]);

        $this->actingAs($this->mine['broker'])->get($url)->assertOk();
        $this->actingAs($this->mine['agent'])->get($url)->assertOk();
        $this->actingAs($admin)->get($url)->assertOk();
        $this->actingAs($this->theirs['broker'])->get($url)->assertNotFound();
        $this->actingAs($this->theirs['agent'])->get($url)->assertNotFound();
        $this->actingAs($this->mine['clientUser'])->get($url)->assertNotFound();

        auth()->logout();
        $this->get($url)->assertRedirect(route('auth.login'));
    }

    public function test_new_commission_proofs_are_saved_privately(): void
    {
        $this->travelTo(now()->startOfMonth()->addDays(14));
        $agreement = $this->mine['agreement'];
        $payment = $agreement->payments()->create(['due_date' => now()->toDateString(), 'amount_due' => 5000, 'agent_amount' => 3000, 'broker_amount' => 2000, 'amount_paid' => 0, 'payment_status' => 'pending']);

        $this->actingAs($this->mine['broker'])->post(route('broker.commissions.update-payment', $agreement), [
            'payment_id' => $payment->id,
            'amount_paid' => 5000,
            'payment_method' => 'cash',
            'proof' => UploadedFile::fake()->create('receipt.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('broker.commissions.show', $agreement));

        $path = $payment->fresh()->proof_path;
        Storage::disk('local')->assertExists($path);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_older_public_files_move_to_private_storage_and_still_open(): void
    {
        $payment = CommissionPayment::find($this->mine['payment']->id);
        Storage::disk('local')->delete($payment->proof_path);
        Storage::disk('public')->put($payment->proof_path, 'receipt');

        $url = route('files.commission-proof', $payment);
        $this->actingAs($this->mine['broker'])->get($url)->assertOk();

        $this->artisan('files:make-private')->assertExitCode(0);

        Storage::disk('local')->assertExists($payment->proof_path);
        Storage::disk('public')->assertMissing($payment->proof_path);
        $this->actingAs($this->mine['broker'])->get($url)->assertOk();
    }

    public function test_moving_files_twice_changes_nothing(): void
    {
        Storage::disk('public')->put('commission-proof/old.pdf', 'receipt');

        Artisan::call('files:make-private');
        Artisan::call('files:make-private');

        $this->assertSame('receipt', Storage::disk('local')->get('commission-proof/old.pdf'));
    }
}
