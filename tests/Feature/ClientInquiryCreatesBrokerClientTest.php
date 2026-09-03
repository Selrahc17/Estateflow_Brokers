<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientInquiryCreatesBrokerClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_inquiries_create_a_broker_client_record(): void
    {
        $broker = User::factory()->create([
            'role' => 'broker',
            'is_active' => true,
            'is_approved' => true,
        ]);

        $property = Property::create([
            'broker_id' => $broker->id,
            'name' => 'Luxury Family House',
            'slug' => 'luxury-family-house',
            'description' => 'A beautiful family home.',
            'address' => '123 Main Street',
            'city' => 'Cebu City',
            'province' => 'Cebu',
            'type' => 'House and Lot',
            'price' => 15000000,
            'status' => 'available',
            'featured_image' => 'https://example.com/house.jpg',
        ]);

        $response = $this->post(route('client.property.inquire', $property), [
            'message' => 'I am interested in this property and would like more details.',
            'phone' => '09171234567',
            'email' => 'client@example.com',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'broker_id' => $broker->id,
            'email' => 'client@example.com',
            'status' => 'active',
        ]);
    }

    public function test_authenticated_inquiry_uses_the_logged_in_client_identity(): void
    {
        $broker = User::factory()->create(['role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $clientUser = User::factory()->create(['role' => 'client', 'email' => 'real-client@example.com']);
        Client::create([
            'user_id' => $clientUser->id,
            'broker_id' => $broker->id,
            'first_name' => 'Real',
            'last_name' => 'Client',
            'email' => $clientUser->email,
            'password' => 'password',
        ]);
        $property = Property::create([
            'broker_id' => $broker->id,
            'name' => 'Secure Listing',
            'slug' => 'secure-listing',
            'type' => 'House and Lot',
            'status' => 'available',
        ]);

        $this->actingAs($clientUser)->post(route('client.property.inquire', $property), [
            'message' => 'I would like more information about this listing.',
            'phone' => '09171234567',
            'email' => 'someone-else@example.com',
        ]);

        $this->assertDatabaseHas('inquiries', [
            'user_id' => $clientUser->id,
            'email' => $clientUser->email,
        ]);
        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $clientUser->id,
            'receiver_id' => $broker->id,
            'message' => 'I would like more information about this listing.',
            'sender_type' => 'user',
        ]);
        $this->assertDatabaseMissing('clients', ['email' => 'someone-else@example.com']);
    }
}
