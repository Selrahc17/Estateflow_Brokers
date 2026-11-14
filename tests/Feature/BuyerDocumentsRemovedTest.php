<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Buyer requirements are handled by the developer's coordinator, not in EstateFlow.
 * The buyer document pages are gone; broker and agent documents stay.
 */
class BuyerDocumentsRemovedTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_buyer_and_agent_document_pages_no_longer_exist(): void
    {
        foreach (['client.account.documents', 'client.account.documents.store', 'client.account.documents.download',
            'agent.documents.index', 'agent.documents.check', 'agent.documents.download', 'agent.documents.verify', 'agent.documents.reject'] as $name) {
            $this->assertFalse(Route::has($name), "{$name} should be removed");
        }

        $this->get('/account/documents')->assertNotFound();
    }

    public function test_menus_no_longer_offer_documents_to_buyers_or_agents(): void
    {
        $buyer = User::factory()->create(['role' => 'client', 'is_active' => true, 'is_approved' => true]);
        $agent = User::factory()->create(['role' => 'agent', 'is_active' => true, 'is_approved' => true]);
        Client::create(['user_id' => $buyer->id, 'broker_id' => $agent->id, 'first_name' => 'Ana', 'last_name' => 'Cruz', 'email' => $buyer->email, 'password' => 'x', 'status' => 'active']);

        $this->actingAs($buyer)->get(route('client.account.home'))
            ->assertOk()
            ->assertDontSee('My Documents')
            ->assertDontSee('Submit your requirements');
        $this->get(route('client.properties'))->assertOk()->assertDontSee('My Documents');

        $this->actingAs($agent)->get(route('agent.dashboard'))->assertOk()->assertDontSee('>Documents<', false);
    }

    public function test_broker_documents_stay_and_old_buyer_uploads_are_hidden_from_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'is_approved' => true]);
        $broker = User::factory()->create(['name' => 'Broker Bea', 'role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $agent = User::factory()->create(['role' => 'agent', 'broker_id' => $broker->id, 'is_active' => true, 'is_approved' => true]);
        $client = Client::create(['broker_id' => $agent->id, 'first_name' => 'Ana', 'last_name' => 'Cruz', 'email' => 'ana@example.com', 'password' => 'x', 'status' => 'active']);
        Document::create(['broker_id' => $broker->id, 'uploaded_by' => $broker->id, 'name' => 'Broker PRC ID', 'type' => 'prc_id', 'file_path' => 'documents/prc.pdf', 'status' => 'pending']);
        Document::create(['client_id' => $client->id, 'name' => 'Old Buyer Valid ID', 'type' => 'id', 'file_path' => 'documents/id.pdf', 'status' => 'pending']);

        $this->actingAs($admin)->get(route('admin.documents'))
            ->assertOk()
            ->assertSee('Broker PRC ID')
            ->assertDontSee('Old Buyer Valid ID')
            ->assertSee('1 Doc to Verify');

        $this->assertSame(2, Document::count(), 'Existing buyer uploads are kept, only hidden');
    }
}
