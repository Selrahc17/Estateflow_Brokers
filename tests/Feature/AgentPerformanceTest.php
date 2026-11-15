<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Sale;
use App\Models\SiteVisit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

/**
 * The broker's agent performance page: activity per agent and a sales ranking
 * per year or quarter that starts fresh every year.
 */
class AgentPerformanceTest extends TestCase
{
    use RefreshDatabase;

    private User $broker;

    private int $year;

    protected function setUp(): void
    {
        parent::setUp();

        $this->year = now()->year;
        $this->broker = User::factory()->create(['name' => 'Broker Bea', 'role' => 'broker', 'is_active' => true, 'is_approved' => true, 'created_at' => Carbon::create($this->year - 3, 1, 1)]);
    }

    private function agent(string $name): User
    {
        return User::factory()->create(['name' => $name, 'role' => 'agent', 'broker_id' => $this->broker->id, 'is_active' => true, 'is_approved' => true]);
    }

    private function sell(User $agent, string $date, float $price = 1000000): Sale
    {
        return Sale::create(['broker_id' => $this->broker->id, 'agent_id' => $agent->id, 'sold_at' => $date, 'selling_price' => $price, 'status' => 'pending']);
    }

    private function date(int $month, int $day = 10, ?int $year = null): string
    {
        return Carbon::create($year ?? $this->year, $month, $day)->toDateString();
    }

    public function test_agents_are_ranked_by_number_of_sales_with_ties_sharing_a_rank(): void
    {
        $ana = $this->agent('Ana');
        $ben = $this->agent('Ben');
        $cara = $this->agent('Cara');
        $dan = $this->agent('Dan');
        $this->agent('Eve');
        // Ana: 3 sales. Ben and Cara: 2 sales of the same total value, so they tie. Dan: 2 sales but less value.
        foreach ([1, 2, 3] as $month) {
            $this->sell($ana, $this->date($month), 500000);
        }
        foreach ([$ben, $cara] as $agent) {
            $this->sell($agent, $this->date(1), 2000000);
            $this->sell($agent, $this->date(2), 2000000);
        }
        $this->sell($dan, $this->date(1), 1000000);
        $this->sell($dan, $this->date(2), 1000000);

        $this->actingAs($this->broker)->get(route('broker.performance'))
            ->assertOk()
            ->assertViewHas('ranking', fn ($ranking) => $ranking->map(fn ($row) => [$row->agent->name, $row->rank, $row->sales])->all() === [
                ['Ana', 1, 3], ['Ben', 2, 2], ['Cara', 2, 2], ['Dan', 4, 2], ['Eve', null, 0],
            ])
            ->assertSee('Top Agent · ' . $this->year)
            ->assertSeeInOrder(['#1', 'Ana', '#2', 'Ben', '#2', 'Cara', '#4', 'Dan', 'Eve']);
    }

    public function test_rankings_start_fresh_each_year_and_past_years_stay_viewable(): void
    {
        $ana = $this->agent('Ana');
        $ben = $this->agent('Ben');
        $this->sell($ana, $this->date(6, 10, $this->year - 1));
        $this->sell($ana, $this->date(7, 10, $this->year - 1));
        $this->sell($ben, $this->date(1));

        $this->actingAs($this->broker)->get(route('broker.performance'))
            ->assertViewHas('topAgents', fn ($top) => $top->pluck('agent.name')->all() === ['Ben'])
            ->assertViewHas('ranking', fn ($ranking) => $ranking->firstWhere('agent.name', 'Ana')->sales === 0);

        $this->get(route('broker.performance', ['year' => $this->year - 1]))
            ->assertSee('Sales Ranking · ' . ($this->year - 1))
            ->assertSee('past year kept for your records')
            ->assertViewHas('topAgents', fn ($top) => $top->pluck('agent.name')->all() === ['Ana'])
            ->assertViewHas('years', fn ($years) => $years === range($this->year, $this->year - 3));
    }

    public function test_a_quarter_can_be_ranked_on_its_own(): void
    {
        $ana = $this->agent('Ana');
        $ben = $this->agent('Ben');
        $this->sell($ana, $this->date(2));
        $this->sell($ana, $this->date(3));
        $this->sell($ben, $this->date(5));

        $this->actingAs($this->broker)->get(route('broker.performance', ['year' => $this->year, 'quarter' => 2]))
            ->assertSee('Sales Ranking · Q2 ' . $this->year)
            ->assertViewHas('topAgents', fn ($top) => $top->pluck('agent.name')->all() === ['Ben'])
            ->assertViewHas('report', fn ($report) => $report->months()->map->format('M')->all() === ['Apr', 'May', 'Jun']);
    }

    public function test_listings_inquiries_viewings_and_monthly_activity_are_counted_for_the_period(): void
    {
        $ana = $this->agent('Ana');
        // created_at is not fillable, so the dates are set after creating.
        $dated = function ($model, string $date) {
            $model->forceFill(['created_at' => $date])->save();

            return $model;
        };
        $property = $dated(Property::create(['broker_id' => $ana->id, 'name' => 'Vista', 'slug' => 'vista', 'type' => 'House and Lot', 'status' => 'available']), $this->date(3));
        $dated(Property::create(['broker_id' => $ana->id, 'name' => 'Old', 'slug' => 'old', 'type' => 'House and Lot', 'status' => 'available']), $this->date(3, 10, $this->year - 1));
        $dated(Inquiry::create(['broker_id' => $ana->id, 'property_id' => $property->id, 'message' => 'Hi', 'email' => 'a@example.com', 'status' => 'new']), $this->date(3));
        $dated(Inquiry::create(['broker_id' => $ana->id, 'property_id' => $property->id, 'message' => 'Hi', 'email' => 'b@example.com', 'status' => 'new']), $this->date(4));
        SiteVisit::create(['broker_id' => $ana->id, 'property_id' => $property->id, 'scheduled_at' => $this->date(4) . ' 10:00:00', 'status' => 'pending']);
        $this->sell($ana, $this->date(4));

        $this->actingAs($this->broker)->get(route('broker.performance'))
            ->assertViewHas('ranking', fn ($ranking) => [$ranking[0]->listings, $ranking[0]->inquiries, $ranking[0]->viewings, $ranking[0]->sales] === [1, 2, 1, 1])
            ->assertViewHas('monthly', fn ($monthly) => $monthly['inquiries'][$ana->id] === [$this->year . '-03' => 1, $this->year . '-04' => 1]
                && $monthly['sales'][$ana->id] === [$this->year . '-04' => 1]
                && $monthly['viewings'][$ana->id] === [$this->year . '-04' => 1])
            ->assertSee('Monthly Activity');
    }

    public function test_other_brokers_agents_and_sales_are_left_out(): void
    {
        $ana = $this->agent('Ana');
        $otherBroker = User::factory()->create(['role' => 'broker']);
        $stranger = User::factory()->create(['name' => 'Stranger Agent', 'role' => 'agent', 'broker_id' => $otherBroker->id]);
        Sale::create(['broker_id' => $otherBroker->id, 'agent_id' => $stranger->id, 'sold_at' => $this->date(1), 'status' => 'pending']);
        Sale::create(['broker_id' => $otherBroker->id, 'agent_id' => $ana->id, 'sold_at' => $this->date(1), 'status' => 'pending']);

        $this->actingAs($this->broker)->get(route('broker.performance'))
            ->assertDontSee('Stranger Agent')
            ->assertViewHas('ranking', fn ($ranking) => $ranking->count() === 1 && $ranking[0]->sales === 0)
            ->assertSee('No sales recorded for ' . $this->year . ' yet.');
    }

    public function test_an_unknown_year_falls_back_to_this_year(): void
    {
        $this->actingAs($this->broker)->get(route('broker.performance', ['year' => 1999, 'quarter' => 9]))
            ->assertViewHas('report', fn ($report) => $report->year === $this->year && $report->quarter === null);
    }

    public function test_the_annual_report_exports_the_ranking_and_monthly_activity(): void
    {
        $ana = $this->agent('Ana');
        $ben = $this->agent('Ben');
        $this->sell($ana, $this->date(1), 3600000);
        $this->sell($ana, $this->date(2), 1400000);
        $this->sell($ben, $this->date(2), 2000000);

        $response = $this->actingAs($this->broker)->get(route('broker.performance.export', ['year' => $this->year]));

        $response->assertOk()->assertDownload("agent-ranking-{$this->year}.xlsx");
        $file = tempnam(sys_get_temp_dir(), 'test') . '.xlsx';
        copy($response->getFile()->getPathname(), $file);
        $book = IOFactory::load($file);

        $ranking = $book->getSheetByName('Ranking');
        $this->assertSame('Agent Sales Ranking · ' . $this->year, $ranking->getCell('A1')->getValue());
        $this->assertSame(['#1', 'Ana', '2', '₱5,000,000.00'], array_slice($ranking->rangeToArray('A5:H5', null, false)[0], 0, 4));
        $this->assertSame(['#2', 'Ben', 1], array_slice($ranking->rangeToArray('A6:H6', null, false, false)[0], 0, 3));
        $this->assertSame('Team total', $ranking->getCell('B7')->getValue());
        $this->assertSame(3, (int) $ranking->getCell('C7')->getCalculatedValue());

        $monthly = $book->getSheetByName('Monthly Activity');
        $this->assertSame(['Ana', 'Sales', 1, 1], array_slice($monthly->rangeToArray('A5:O5', null, false, false)[0], 0, 4));
        $this->assertSame('Total', $monthly->getCell('O4')->getValue());
        $this->assertSame(2, (int) $monthly->getCell('O5')->getValue());
        @unlink($file);
    }

    public function test_reports_show_sales_value_instead_of_buyer_payments(): void
    {
        $ana = $this->agent('Ana');
        $this->sell($ana, now()->startOfYear()->toDateString(), 3600000);

        $this->actingAs($this->broker)->get(route('broker.reports.index'))
            ->assertSeeInOrder(['Sales Value', '₱3,600,000.00'])
            ->assertSee('Monthly Sales Value (' . $this->year . ')')
            ->assertDontSee('Monthly Revenue');
    }
}
