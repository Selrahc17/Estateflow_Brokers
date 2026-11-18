<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * The admin Documents page is a review queue: brokers upload, admins review.
 */
class AdminDocumentReviewTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $bea;

    private User $carlo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true, 'is_approved' => true]);
        $this->bea = User::factory()->create(['name' => 'Bea Broker', 'email' => 'bea@example.com', 'role' => 'broker', 'is_active' => true, 'is_approved' => false]);
        $this->carlo = User::factory()->create(['name' => 'Carlo Broker', 'email' => 'carlo@example.com', 'role' => 'broker', 'is_active' => true, 'is_approved' => true]);
    }

    private function doc(User $broker, string $type, string $status, array $extra = []): Document
    {
        return Document::create($extra + [
            'broker_id' => $broker->id,
            'uploaded_by' => $broker->id,
            'name' => Document::typeLabels()[$type] ?? $type,
            'type' => $type,
            'file_path' => "broker-compliance/{$broker->id}/{$type}.pdf",
            'file_size' => '120 KB',
            'status' => $status,
        ]);
    }

    public function test_admins_can_no_longer_upload_broker_documents(): void
    {
        $this->assertFalse(Route::has('admin.documents.store'));

        $this->actingAs($this->admin)->get(route('admin.documents'))
            ->assertOk()
            ->assertDontSee('Upload Broker Document')
            ->assertDontSee('enctype="multipart/form-data"', false);
    }

    public function test_the_pending_tab_is_shown_first_with_counts_for_every_status(): void
    {
        $this->doc($this->bea, 'prc_id', 'pending');
        $this->doc($this->bea, 'bond', 'verified');
        $this->doc($this->carlo, 'ptr', 'needs_more', ['notes' => 'Blurry copy']);

        $page = $this->actingAs($this->admin)->get(route('admin.documents'))->assertOk();

        $page->assertViewHas('status', 'pending')
            ->assertViewHas('counts', fn ($counts) => $counts['pending'] === 1 && $counts['verified'] === 1 && $counts['needs_more'] === 1 && $counts['all'] === 3)
            ->assertSee('Bea Broker')
            ->assertDontSee('Carlo Broker')
            ->assertSee('Request new copy')
            ->assertSee('Reject document');

        $this->get(route('admin.documents', ['status' => 'needs_more']))
            ->assertSee('Carlo Broker')
            ->assertSee('Blurry copy')
            ->assertSee('Waiting for the broker to upload a new copy.')
            ->assertDontSee('Bea Broker');
    }

    public function test_each_broker_shows_their_compliance_checklist(): void
    {
        $this->doc($this->bea, 'prc_id', 'pending');
        $this->doc($this->bea, 'bond', 'verified');

        $this->actingAs($this->admin)->get(route('admin.documents'))
            ->assertSee('1 of 5 verified')
            ->assertSee('Awaiting approval')
            ->assertSeeInOrder(['PRC ID', 'Pending review', 'Bond or Indemnity Insurance', 'Verified', 'BIR Form 2303', 'Not uploaded'])
            ->assertSee(route('admin.brokers.show', $this->bea), false);
    }

    public function test_search_and_type_filters_narrow_the_queue(): void
    {
        $this->doc($this->bea, 'prc_id', 'pending');
        $this->doc($this->carlo, 'bir_2303', 'pending');

        $this->actingAs($this->admin)->get(route('admin.documents', ['q' => 'carlo@']))
            ->assertSee('Carlo Broker')->assertDontSee('Bea Broker');

        $this->get(route('admin.documents', ['type' => 'prc_id']))
            ->assertSee('Bea Broker')->assertDontSee('Carlo Broker');

        $this->get(route('admin.documents', ['q' => 'nobody']))
            ->assertSee('No documents match your search');
    }

    public function test_agent_ids_show_which_agent_they_belong_to(): void
    {
        $agent = User::factory()->create(['name' => 'Ryan Agent', 'role' => 'agent', 'broker_id' => $this->carlo->id, 'is_active' => true, 'is_approved' => true]);
        $this->doc($this->carlo, 'national_id', 'pending', ['agent_id' => $agent->id, 'name' => 'National id', 'file_size' => 2048000]);

        $this->actingAs($this->admin)->get(route('admin.documents'))
            ->assertSee('National ID')
            ->assertDontSee('National id')
            ->assertSee('For agent Ryan Agent')
            ->assertSee('2 MB');
    }

    public function test_an_empty_queue_says_there_is_nothing_to_review(): void
    {
        $this->doc($this->bea, 'prc_id', 'verified');

        $this->actingAs($this->admin)->get(route('admin.documents'))
            ->assertSee('Nothing to review')
            ->assertDontSee('Bea Broker');
    }

    public function test_review_actions_name_the_document(): void
    {
        $document = $this->doc($this->bea, 'prc_id', 'pending');

        $this->actingAs($this->admin)->from(route('admin.documents'))
            ->post(route('admin.documents.request-more', $document), ['notes' => 'Please upload the back page'])
            ->assertSessionHas('success', 'Asked the broker for a new copy of PRC ID.');
        $this->assertSame('needs_more', $document->fresh()->status);

        $this->followingRedirects()->post(route('admin.documents.verify', $this->doc($this->bea, 'bond', 'pending')))
            ->assertSee('Bond or Indemnity Insurance verified.');
    }
}
