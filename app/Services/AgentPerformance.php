<?php

namespace App\Services;

use App\Models\Inquiry;
use App\Models\Property;
use App\Models\Sale;
use App\Models\SiteVisit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

/**
 * Each agent's listings, inquiries, viewings and sales for one year or quarter,
 * ranked by number of sales. Rankings start over with every period, and any
 * past year can be looked up again because nothing is deleted.
 */
class AgentPerformance
{
    public readonly CarbonImmutable $start;

    public readonly CarbonImmutable $end;

    public function __construct(
        public readonly User $broker,
        public readonly int $year,
        public readonly ?int $quarter = null,
    ) {
        $first = CarbonImmutable::create($year, $quarter ? ($quarter - 1) * 3 + 1 : 1, 1)->startOfDay();
        $this->start = $first;
        $this->end = $quarter ? $first->addMonths(2)->endOfMonth() : $first->endOfYear();
    }

    /** "2026" or "Q2 2026". */
    public function label(): string
    {
        return $this->quarter ? "Q{$this->quarter} {$this->year}" : (string) $this->year;
    }

    /** Years the broker can look back on, newest first. */
    public static function availableYears(User $broker): array
    {
        $firstSale = Sale::forBroker($broker)->min('sold_at');
        $from = min((int) $broker->created_at?->year ?: now()->year, $firstSale ? (int) substr((string) $firstSale, 0, 4) : now()->year);

        return range(now()->year, min($from, now()->year));
    }

    /** @return Collection<int, CarbonImmutable> The first day of each month in the period. */
    public function months(): Collection
    {
        $count = $this->quarter ? 3 : 12;

        return collect(range(0, $count - 1))->map(fn (int $offset) => $this->start->addMonths($offset));
    }

    /**
     * One row per agent with their numbers and rank. Agents with no sales are
     * listed last without a rank. Ties on sales and sales value share a rank.
     *
     * @return Collection<int, object>
     */
    public function ranking(): Collection
    {
        $agents = $this->agents();
        $ids = $agents->pluck('id');
        $countBy = fn ($query, string $column, string $date) => $query->whereIn($column, $ids)
            ->whereBetween($date, [$this->start, $this->end])
            ->selectRaw("{$column} as agent, count(*) as total")->groupBy($column)->pluck('total', 'agent');

        $listings = $countBy(Property::query(), 'broker_id', 'created_at');
        $inquiries = $countBy(Inquiry::query(), 'broker_id', 'created_at');
        $viewings = $countBy(SiteVisit::query(), 'broker_id', 'scheduled_at');
        $sales = Sale::forBroker($this->broker)->whereIn('agent_id', $ids)->whereBetween('sold_at', [$this->start->toDateString(), $this->end->toDateString()])
            ->toBase()
            ->selectRaw("agent_id, count(*) as sales, coalesce(sum(selling_price), 0) as value, coalesce(sum(agent_commission), 0) as commission, coalesce(sum(case when status = 'received' then agent_commission else 0 end), 0) as received")
            ->groupBy('agent_id')->get()->keyBy('agent_id');

        $rows = $agents->map(fn (User $agent) => (object) [
            'agent' => $agent,
            'listings' => (int) $listings->get($agent->id, 0),
            'inquiries' => (int) $inquiries->get($agent->id, 0),
            'viewings' => (int) $viewings->get($agent->id, 0),
            'sales' => (int) ($sales->get($agent->id)->sales ?? 0),
            'sales_value' => (float) ($sales->get($agent->id)->value ?? 0),
            'commission' => (float) ($sales->get($agent->id)->commission ?? 0),
            'commission_received' => (float) ($sales->get($agent->id)->received ?? 0),
            'rank' => null,
        ])->sort(fn ($a, $b) => [$b->sales, $b->sales_value, $a->agent->name] <=> [$a->sales, $a->sales_value, $b->agent->name])->values();

        // Standard competition ranking: 1, 2, 2, 4. No sales, no rank.
        $previous = null;
        foreach ($rows as $position => $row) {
            if ($row->sales === 0) {
                break;
            }
            $row->rank = $previous && [$previous->sales, $previous->sales_value] === [$row->sales, $row->sales_value] ? $previous->rank : $position + 1;
            $previous = $row;
        }

        return $rows;
    }

    /** Everyone ranked first: the period's top agent, or several if tied. */
    public function topAgents(Collection $ranking): Collection
    {
        return $ranking->where('rank', 1)->values();
    }

    /**
     * Sales, inquiries and viewings per agent per month.
     *
     * @return array<string, array<int, array<string, int>>> metric => agent id => "Y-m" => count
     */
    public function monthly(): array
    {
        $ids = $this->agents()->pluck('id');
        $perMonth = fn (Collection $rows, string $agentColumn, string $dateColumn) => $rows
            ->groupBy($agentColumn)
            ->map(fn ($items) => $items->countBy(fn ($item) => CarbonImmutable::parse($item->{$dateColumn})->format('Y-m'))->all())
            ->all();

        return [
            'sales' => $perMonth(Sale::forBroker($this->broker)->whereIn('agent_id', $ids)->whereBetween('sold_at', [$this->start->toDateString(), $this->end->toDateString()])->toBase()->get(['agent_id', 'sold_at']), 'agent_id', 'sold_at'),
            'inquiries' => $perMonth(Inquiry::whereIn('broker_id', $ids)->whereBetween('created_at', [$this->start, $this->end])->toBase()->get(['broker_id', 'created_at']), 'broker_id', 'created_at'),
            'viewings' => $perMonth(SiteVisit::whereIn('broker_id', $ids)->whereBetween('scheduled_at', [$this->start, $this->end])->toBase()->get(['broker_id', 'scheduled_at']), 'broker_id', 'scheduled_at'),
        ];
    }

    private ?Collection $agentsCache = null;

    private function agents(): Collection
    {
        return $this->agentsCache ??= User::where('role', 'agent')->where('broker_id', $this->broker->id)->orderBy('name')->get(['id', 'name', 'email', 'is_active']);
    }
}
