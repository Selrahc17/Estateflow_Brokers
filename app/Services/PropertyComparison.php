<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Collection;

/**
 * Builds the side-by-side comparison of listings: grouped rows of facts, the
 * best value in each row, and a plain summary when AI is not available.
 */
class PropertyComparison
{
    public const MAX = 4;

    /**
     * @return array<string, array<int, array{label: string, values: array<int, ?string>, best: array<int, int>}>>
     */
    public function sections(Collection $properties): array
    {
        $sections = [];

        foreach ($this->definitions() as $section => $rows) {
            foreach ($rows as [$label, $value, $format, $better]) {
                $raw = $properties->map(fn (Property $property) => $value($property))->values();

                // Skip rows that no compared listing fills in.
                if ($raw->filter(fn ($item) => $item !== null && $item !== '' && $item !== [])->isEmpty()) {
                    continue;
                }

                $sections[$section][] = [
                    'label' => $label,
                    'values' => $raw->map(fn ($item) => $item === null || $item === '' || $item === [] ? null : $format($item))->all(),
                    'best' => $better ? $this->best($raw, $better) : [],
                ];
            }
        }

        return $sections;
    }

    /**
     * The same facts as plain text per listing, for the AI prompt.
     *
     * @return array<int, array<string, string>>
     */
    public function facts(Collection $properties): array
    {
        $sections = $this->sections($properties);

        return $properties->values()->map(function (Property $property, int $index) use ($sections) {
            $facts = ['Name' => $property->name];
            foreach ($sections as $rows) {
                foreach ($rows as $row) {
                    if ($row['values'][$index] !== null) {
                        $facts[$row['label']] = $row['values'][$index];
                    }
                }
            }

            return $facts;
        })->all();
    }

    /** A short summary from the numbers alone, used when AI is off or fails. */
    public function quickSummary(Collection $properties): string
    {
        $names = $properties->values()->pluck('name');
        $lines = [];

        foreach ($this->sections($properties) as $rows) {
            foreach ($rows as $row) {
                if (count($row['best']) !== 1) {
                    continue;
                }
                $index = $row['best'][0];
                $lines[] = match ($row['label']) {
                    'Starting price' => "{$names[$index]} has the lowest starting price ({$row['values'][$index]}).",
                    'Floor area' => "{$names[$index]} has the largest floor area ({$row['values'][$index]}).",
                    'Lot area' => "{$names[$index]} has the largest lot ({$row['values'][$index]}).",
                    'Bedrooms' => "{$names[$index]} has the most bedrooms ({$row['values'][$index]}).",
                    'Reservation fee' => "{$names[$index]} has the lowest reservation fee ({$row['values'][$index]}).",
                    'Client rating' => "{$names[$index]} has the best client rating ({$row['values'][$index]}).",
                    default => null,
                };
            }
        }

        $lines = array_values(array_filter($lines));

        return $lines
            ? implode("\n", $lines)
            : 'These listings are close on the details we have. Compare the photos and locations, or ask the agents for more details.';
    }

    /**
     * @return array<string, array<int, array{0: string, 1: callable, 2: callable, 3: ?string}>>
     */
    private function definitions(): array
    {
        $peso = fn ($value) => '₱'.number_format((float) $value, 0);
        $sqm = fn ($value) => rtrim(rtrim(number_format((float) $value, 2), '0'), '.').' sqm';
        $text = fn ($value) => (string) $value;
        $number = fn ($value) => (string) (int) $value;

        return [
            'Price and payment' => [
                ['Starting price', fn (Property $p) => ((float) $p->price ?: $p->lowest_model_price) ?: null, $peso, 'low'],
                ['Reservation fee', fn (Property $p) => (float) $p->reservation_fee ?: null, $peso, 'low'],
                ['Down payment', fn (Property $p) => $p->downpayment_percent_label, $text, null],
                ['Cash discount', fn (Property $p) => $p->cash_discount_label, $text, null],
                ['Payment options', fn (Property $p) => implode(', ', $p->payment_option_labels) ?: null, $text, null],
            ],
            'About the property' => [
                ['Type', fn (Property $p) => $p->type, $text, null],
                ['Selling stage', fn (Property $p) => $p->selling_stage_label, $text, null],
                ['Developer', fn (Property $p) => $p->developer, $text, null],
                ['Location', fn (Property $p) => implode(', ', array_filter([$p->city, $p->province])) ?: null, $text, null],
                ['Available lots or units', fn (Property $p) => $p->lots_count ?: null, $number, 'high'],
            ],
            'Size and rooms' => [
                ['Lot area', fn (Property $p) => (float) $p->lot_area ?: null, $sqm, 'high'],
                ['Floor area', fn (Property $p) => (float) $p->floor_area ?: null, $sqm, 'high'],
                ['Bedrooms', fn (Property $p) => $p->bedrooms ?: null, $number, 'high'],
                ['Bathrooms', fn (Property $p) => $p->bathrooms ?: null, $number, 'high'],
                ['Stories', fn (Property $p) => $p->stories ?: null, $number, null],
                ['Parking slots', fn (Property $p) => $p->parking_slots ?: null, $number, 'high'],
            ],
            'Features' => [
                ['Amenities', fn (Property $p) => implode(', ', array_filter((array) $p->amenities)) ?: null, $text, null],
                ['Nearby', fn (Property $p) => collect((array) $p->landmarks)->take(3)
                    ->map(fn ($place) => trim(($place['name'] ?? '').(filled($place['travel_time'] ?? null) ? " ({$place['travel_time']})" : '')))
                    ->filter()->implode(', ') ?: null, $text, null],
            ],
            'Reviews' => [
                ['Client rating', fn (Property $p) => ($p->rating_count ?? 0) > 0 ? round((float) $p->rating_avg, 1) : null,
                    fn ($value) => number_format((float) $value, 1).' / 5', 'high'],
            ],
        ];
    }

    /** @return array<int, int> Positions holding the best value, if the values differ. */
    private function best(Collection $raw, string $better): array
    {
        $numbers = $raw->map(fn ($value) => is_numeric($value) ? (float) $value : null)->filter(fn ($value) => $value !== null);

        if ($numbers->count() < 2 || $numbers->unique()->count() < 2) {
            return [];
        }

        $target = $better === 'low' ? $numbers->min() : $numbers->max();

        return $numbers->filter(fn ($value) => $value === $target)->keys()->values()->all();
    }
}
