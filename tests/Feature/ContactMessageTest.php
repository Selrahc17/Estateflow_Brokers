<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_contact_form_stores_a_message(): void
    {
        $response = $this->post(route('client.contact.store'), [
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'juan@example.com',
            'phone' => '09171234567',
            'subject' => 'General Inquiry',
            'message' => 'I would like to learn more about your properties.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contact_messages', [
            'email' => 'juan@example.com',
            'subject' => 'General Inquiry',
            'status' => 'new',
        ]);
    }
}
