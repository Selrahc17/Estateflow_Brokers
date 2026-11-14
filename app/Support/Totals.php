<?php

namespace App\Support;

use Illuminate\Contracts\Database\Query\Builder as BuilderContract;
use Illuminate\Support\Facades\DB;

/**
 * Runs several counts or sums in one database round trip.
 *
 * Each query is a trip to the database, which is slow when the database is far
 * away, so a page that shows six numbers should ask for them all at once.
 *
 *     Totals::of([
 *         'clients' => Client::where('broker_id', $id)->selectRaw('count(*)'),
 *         'revenue' => Payment::where('status', 'verified')->selectRaw('coalesce(sum(amount), 0)'),
 *     ]);
 */
class Totals
{
    /**
     * @param  array<string, BuilderContract>  $queries  Each query must select a single value.
     * @return array<string, float|int>
     */
    public static function of(array $queries): array
    {
        if ($queries === []) {
            return [];
        }

        $query = DB::query();
        foreach ($queries as $name => $subquery) {
            $query->selectSub($subquery, $name);
        }

        $row = (array) $query->first();

        return array_map(function ($value) {
            $number = (float) ($value ?? 0);

            return floor($number) === $number && abs($number) < PHP_INT_MAX ? (int) $number : $number;
        }, array_intersect_key($row, $queries));
    }
}
