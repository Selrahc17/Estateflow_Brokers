<?php

namespace Tests\Feature;

use App\Models\Lot;
use App\Models\Property;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * The broker's commission table: sales are recorded when an agent marks a
 * listing or lot as Sold, or by the broker by hand. Amounts stay private.
 */
class SalesCommissionTest extends TestCase
{
    use RefreshDatabase;

    private User $broker;

    private User $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->broker = User::factory()->create(['name' => 'Broker Bea', 'role' => 'broker', 'is_active' => true, 'is_approved' => true]);
        $this->agent = User::factory()->create(['name' => 'Agent Ana', 'role' => 'agent', 'broker_id' => $this->broker->id, 'is_active' => true, 'is_approved' => true]);
    }

    private function listing(array $overrides = []): Property
    {
        return Property::create(array_merge(['broker_id' => $this->agent->id, 'name' => 'Vista Townhomes', 'slug' => uniqid('vista-'), 'type' => 'House and Lot', 'status' => 'available', 'price' => 3600000], $overrides));
    }

    private function png(): string
    {
        $image = imagecreatetruecolor(40, 30);
        imagefill($image, 0, 0, imagecolorallocate($image, 20, 120, 130));
        ob_start();
        imagepng($image);

        return 'data:image/png;base64,' . base64_encode(ob_get_clean());
    }

    public function test_marking_a_listing_sold_adds_it_to_the_brokers_records(): void
    {
        $property = $this->listing();

        $this->actingAs($this->agent)->patch(route('agent.properties.mark-sold', $property))->assertSessionHas('success');

        $sale = Sale::sole();
        $this->assertSame('sold', $property->fresh()->status);
        $this->assertSame([$this->broker->id, $this->agent->id, $property->id, null, '3600000.00', 'pending', 'auto'],
            [$sale->broker_id, $sale->agent_id, $sale->property_id, $sale->lot_id, $sale->selling_price, $sale->status, $sale->source]);
        $this->assertTrue($sale->sold_at->isToday());
        $this->assertFalse($sale->show_status_to_agent);
    }

    public function test_choosing_sold_on_the_edit_form_also_records_the_sale(): void
    {
        $property = $this->listing();

        $this->actingAs($this->agent)->put(route('agent.properties.update', $property), ['name' => 'Vista Townhomes', 'type' => 'House and Lot', 'status' => 'sold'])->assertSessionHasNoErrors();

        $this->assertSame(1, Sale::where('property_id', $property->id)->count());
    }

    public function test_each_sold_lot_is_its_own_sale_with_its_own_price(): void
    {
        $property = $this->listing(['name' => 'Villa Cassandra', 'type' => 'Subdivision Lot Only', 'price' => 1320000]);
        $lot13 = Lot::create(['property_id' => $property->id, 'lot_number' => 'Blk 25 Lot 13', 'status' => 'available', 'price' => 1440000, 'square_meters' => 120]);
        $lot32 = Lot::create(['property_id' => $property->id, 'lot_number' => 'Blk 20 Lot 32', 'status' => 'available']);

        $this->actingAs($this->agent)->patch(route('agent.lots.mark-sold', $lot13))->assertSessionHas('success');
        $this->put(route('agent.lots.update', $lot32), ['property_id' => $property->id, 'lot_number' => 'Blk 20 Lot 32', 'status' => 'sold'])->assertSessionHasNoErrors();

        $this->assertSame(['1440000.00', '1320000.00'], Sale::orderBy('id')->pluck('selling_price')->all(), 'A lot without a price uses the listing price');
        $this->assertSame('Villa Cassandra · Blk 25 Lot 13', Sale::first()->item_name);

        // A listing with lots is sold lot by lot, not as a whole.
        $this->patch(route('agent.properties.mark-sold', $property))->assertSessionHas('error');
        $this->assertSame(2, Sale::count());
    }

    public function test_undoing_sold_removes_an_untouched_record_but_keeps_one_the_broker_worked_on(): void
    {
        $untouched = $this->listing();
        $worked = $this->listing(['name' => 'Galicia Homes']);
        $this->actingAs($this->agent);
        $this->patch(route('agent.properties.mark-sold', $untouched));
        $this->patch(route('agent.properties.mark-sold', $worked));
        Sale::where('property_id', $worked->id)->update(['agent_commission' => 50000]);

        $untouched->refresh()->update(['status' => 'available']);
        $worked->refresh()->update(['status' => 'available']);

        $this->assertDatabaseMissing('sales', ['property_id' => $untouched->id]);
        $this->assertDatabaseHas('sales', ['property_id' => $worked->id]);

        // Marking it sold again does not add a second row.
        $worked->update(['status' => 'sold']);
        $this->assertSame(1, Sale::where('property_id', $worked->id)->count());
    }

    public function test_a_listing_under_admin_review_cannot_be_marked_sold(): void
    {
        $property = $this->listing(['status' => 'flagged']);

        $this->actingAs($this->agent)->patch(route('agent.properties.mark-sold', $property))->assertSessionHas('error');

        $this->assertSame('flagged', $property->fresh()->status);
        $this->assertSame(0, Sale::count());
    }

    public function test_the_broker_records_edits_and_removes_a_sale(): void
    {
        $property = $this->listing();
        $this->actingAs($this->broker);

        $this->post(route('broker.commissions.store'), [
            'agent_id' => $this->agent->id, 'property_id' => $property->id, 'sold_at' => now()->subDays(3)->toDateString(),
            'selling_price' => 3500000, 'agent_commission' => 70000, 'broker_commission' => 105000, 'status' => 'pending', 'notes' => 'Spot cash',
        ])->assertRedirect(route('broker.commissions.index'));

        $sale = Sale::sole();
        $this->assertSame(['manual', $this->broker->id, '70000.00'], [$sale->source, $sale->broker_id, $sale->agent_commission]);

        $this->get(route('broker.commissions.edit', $sale))->assertOk()->assertSee('Spot cash');
        $this->put(route('broker.commissions.update', $sale), [
            'agent_id' => $this->agent->id, 'property_id' => $property->id, 'sold_at' => $sale->sold_at->toDateString(),
            'selling_price' => 3500000, 'status' => 'transferred', 'show_status_to_agent' => '1',
        ])->assertRedirect(route('broker.commissions.index'));
        $this->assertSame(['transferred', true, null], [$sale->fresh()->status, $sale->fresh()->show_status_to_agent, $sale->fresh()->agent_commission]);

        $this->delete(route('broker.commissions.destroy', $sale))->assertRedirect(route('broker.commissions.index'));
        $this->assertSame(0, Sale::count());
    }

    public function test_the_broker_cannot_record_sales_for_other_teams_or_bad_values(): void
    {
        $otherAgent = User::factory()->create(['role' => 'agent', 'broker_id' => User::factory()->create(['role' => 'broker'])->id]);
        $otherListing = Property::create(['broker_id' => $otherAgent->id, 'name' => 'Not Ours', 'slug' => 'not-ours', 'type' => 'House and Lot', 'status' => 'available']);
        $property = $this->listing();
        $valid = ['agent_id' => $this->agent->id, 'property_id' => $property->id, 'sold_at' => now()->toDateString(), 'status' => 'pending'];
        $this->actingAs($this->broker);

        $this->post(route('broker.commissions.store'), ['agent_id' => $otherAgent->id] + $valid)->assertSessionHasErrors('agent_id');
        $this->post(route('broker.commissions.store'), ['property_id' => $otherListing->id] + $valid)->assertSessionHasErrors('property_id');
        $this->post(route('broker.commissions.store'), ['sold_at' => now()->addDay()->toDateString()] + $valid)->assertSessionHasErrors('sold_at');
        $this->post(route('broker.commissions.store'), ['status' => 'paid'] + $valid)->assertSessionHasErrors('status');
        $this->assertSame(0, Sale::count());
    }

    public function test_the_broker_page_shows_totals_and_filters(): void
    {
        $other = User::factory()->create(['name' => 'Agent Ben', 'role' => 'agent', 'broker_id' => $this->broker->id]);
        $property = $this->listing();
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'property_id' => $property->id, 'sold_at' => now(), 'selling_price' => 3600000, 'agent_commission' => 72000, 'broker_commission' => 108000, 'status' => 'pending']);
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $other->id, 'property_id' => $property->id, 'sold_at' => now(), 'selling_price' => 1400000, 'agent_commission' => 28000, 'status' => 'received']);

        $this->actingAs($this->broker)->get(route('broker.commissions.index'))
            ->assertOk()
            ->assertSeeInOrder(['Total Selling Price', '₱5,000,000.00', 'Agent Commission', '₱100,000.00', 'Broker Commission', '₱108,000.00'])
            ->assertSee('Agent Ana')->assertSee('Agent Ben');

        $this->get(route('broker.commissions.index', ['status' => 'received']))->assertSee('Agent Ben')->assertDontSee('₱72,000.00');
        $this->get(route('broker.commissions.index', ['agent' => $this->agent->id]))->assertSee('₱72,000.00')->assertDontSee('₱28,000.00');
    }

    public function test_agents_see_their_own_sales_without_amounts_and_status_only_when_shared(): void
    {
        $other = User::factory()->create(['role' => 'agent', 'broker_id' => $this->broker->id]);
        $shared = $this->listing(['name' => 'Shared Status Homes']);
        $private = $this->listing(['name' => 'Private Status Homes']);
        $notMine = Property::create(['broker_id' => $other->id, 'name' => 'Someone Elses Sale', 'slug' => 'else', 'type' => 'House and Lot', 'status' => 'sold']);
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'property_id' => $shared->id, 'sold_at' => now(), 'selling_price' => 3600000, 'agent_commission' => 72345, 'broker_commission' => 108765, 'status' => 'transferred', 'show_status_to_agent' => true]);
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'property_id' => $private->id, 'sold_at' => now(), 'agent_commission' => 11111, 'status' => 'received']);
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $other->id, 'property_id' => $notMine->id, 'sold_at' => now(), 'status' => 'pending']);

        $page = $this->actingAs($this->agent)->get(route('agent.commission'))->assertOk();

        $page->assertSee('Shared Status Homes')->assertSee('Private Status Homes')->assertDontSee('Someone Elses Sale')
            ->assertSee('Transferred')->assertDontSee('Received')->assertSee('Not shared by your broker')
            ->assertSee('₱3,600,000.00');
        foreach (['72,345', '108,765', '11,111'] as $amount) {
            $page->assertDontSee($amount);
        }

        // Agents cannot open or change the broker's records.
        $sale = Sale::first();
        $this->get(route('broker.commissions.index'))->assertForbidden();
        $this->get(route('broker.commissions.edit', $sale))->assertForbidden();
        $this->put(route('broker.commissions.update', $sale), ['status' => 'received'])->assertForbidden();
        $this->get(route('broker.commissions.export'))->assertForbidden();
        $this->assertSame('transferred', $sale->fresh()->status);
    }

    public function test_the_excel_export_has_every_column_a_photo_and_totals(): void
    {
        $property = $this->listing(['featured_image' => $this->png()]);
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'property_id' => $property->id, 'sold_at' => '2026-11-10', 'selling_price' => 3600000, 'agent_commission' => 72000, 'broker_commission' => 108000, 'status' => 'transferred', 'notes' => 'Bank financing']);
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'property_id' => $this->listing(['name' => 'No Photo Homes'])->id, 'sold_at' => '2026-11-01', 'selling_price' => 1400000, 'status' => 'pending']);
        Sale::create(['broker_id' => User::factory()->create(['role' => 'broker'])->id, 'sold_at' => now(), 'selling_price' => 999, 'status' => 'pending']);

        $response = $this->actingAs($this->broker)->get(route('broker.commissions.export'));

        $response->assertOk()->assertDownload('commission-records-' . now()->format('Y-m-d') . '.xlsx');
        $file = tempnam(sys_get_temp_dir(), 'test') . '.xlsx';
        copy($response->getFile()->getPathname(), $file);
        $sheet = IOFactory::load($file)->getActiveSheet();

        $this->assertSame(['Agent Name', 'Photo', 'Property / Project', 'Date of Sale', 'Selling Price', 'Agent Commission', 'Broker Commission', 'Commission Status', 'Notes'], $sheet->rangeToArray('A4:I4')[0]);
        $this->assertSame(['Agent Ana', 'Vista Townhomes', '2026-11-10', '₱3,600,000.00', '₱72,000.00', '₱108,000.00', 'Transferred', 'Bank financing'],
            array_values(array_filter($sheet->rangeToArray('A5:I5', null, false)[0], fn ($cell) => $cell !== null)));
        $this->assertSame('No Photo Homes', $sheet->getCell('C6')->getValue());
        $this->assertSame('Total (2 sales)', $sheet->getCell('A7')->getValue(), "Another broker's sale is not exported");
        $this->assertSame(5000000, (int) $sheet->getCell('E7')->getCalculatedValue());
        $this->assertCount(1, $sheet->getDrawingCollection(), 'The listing with a photo gets a thumbnail; the one without is left blank');
        $this->assertSame('B5', $sheet->getDrawingCollection()[0]->getCoordinates());
        @unlink($file);
    }

    public function test_the_export_follows_the_filters_on_screen(): void
    {
        $property = $this->listing();
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'property_id' => $property->id, 'sold_at' => now(), 'status' => 'pending']);
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'property_id' => $property->id, 'sold_at' => now(), 'status' => 'received']);

        $response = $this->actingAs($this->broker)->get(route('broker.commissions.export', ['status' => 'received']));
        $file = tempnam(sys_get_temp_dir(), 'test') . '.xlsx';
        copy($response->getFile()->getPathname(), $file);

        $this->assertSame('Total (1 sale)', IOFactory::load($file)->getActiveSheet()->getCell('A6')->getValue());
        @unlink($file);
    }

    public function test_reports_and_performance_use_the_sales_records(): void
    {
        $property = $this->listing();
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'property_id' => $property->id, 'sold_at' => now(), 'agent_commission' => 60000, 'broker_commission' => 40000, 'status' => 'received']);
        Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $this->agent->id, 'property_id' => $property->id, 'sold_at' => now(), 'agent_commission' => 10000, 'broker_commission' => 5000, 'status' => 'pending']);
        $this->actingAs($this->broker);

        $this->get(route('broker.reports.index'))->assertSeeInOrder(['Commission Pending', '₱15,000.00', 'Commission Transferred', '₱0.00', 'Commission Received', '₱100,000.00']);
        $this->get(route('broker.performance'))->assertSee('₱60,000.00 received')->assertSee('of ₱70,000.00 agent commission');
        $this->get(route('broker.dashboard'))->assertViewHas('stats', fn (array $stats) => $stats['sales_this_month'] === 2);
    }
}
