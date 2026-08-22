<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Property;
use App\Models\SiteVisit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientSiteVisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_request_a_site_visit_for_an_available_property(): void
    {
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $clientUser = User::factory()->create(['role' => 'client', 'is_active' => true]);
        Client::create([
            'user_id' => $clientUser->id,
            'broker_id' => $broker->id,
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => $clientUser->email,
            'password' => 'password',
        ]);
        $property = Property::create([
            'broker_id' => $broker->id,
            'name' => 'Family Home',
            'slug' => 'family-home',
            'type' => 'House and Lot',
            'price' => 5000000,
            'status' => 'available',
        ]);

        $response = $this->actingAs($clientUser)->post(route('client.account.site-visits.store', $property), [
            'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'notes' => 'Afternoon viewing preferred.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('site_visits', [
            'client_id' => $clientUser->clientProfile->id,
            'property_id' => $property->id,
            'broker_id' => $broker->id,
            'status' => 'pending',
        ]);
    }

    public function test_client_cannot_cancel_another_clients_site_visit(): void
    {
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $clientUser = User::factory()->create(['role' => 'client', 'is_active' => true]);
        $otherUser = User::factory()->create(['role' => 'client', 'is_active' => true]);
        $client = Client::create(['user_id' => $clientUser->id, 'broker_id' => $broker->id, 'first_name' => 'A', 'last_name' => 'Client', 'email' => $clientUser->email, 'password' => 'password']);
        $otherClient = Client::create(['user_id' => $otherUser->id, 'broker_id' => $broker->id, 'first_name' => 'B', 'last_name' => 'Client', 'email' => $otherUser->email, 'password' => 'password']);
        $property = Property::create(['broker_id' => $broker->id, 'name' => 'Family Home', 'slug' => 'family-home', 'type' => 'House and Lot', 'status' => 'available']);
        $siteVisit = SiteVisit::create(['client_id' => $otherClient->id, 'property_id' => $property->id, 'broker_id' => $broker->id, 'scheduled_at' => now()->addDay(), 'status' => 'pending']);

        $response = $this->actingAs($clientUser)->patch(route('client.account.site-visits.cancel', $siteVisit));

        $response->assertForbidden();
        $this->assertDatabaseHas('site_visits', ['id' => $siteVisit->id, 'status' => 'pending']);
    }
}
