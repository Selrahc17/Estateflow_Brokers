<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("SET statement_timeout = '600000'");
        }

        $columns = [
            ['bedrooms', 'integer'],
            ['bathrooms', 'integer'],
            ['floor_area', 'numeric(10,2)'],
            ['lot_area', 'numeric(10,2)'],
            ['frontage', 'numeric(10,2)'],
            ['stories', 'integer'],
            ['parking_slots', 'integer'],
        ];

        if (DB::getDriverName() === 'sqlite') {
            $existingColumns = collect(DB::select('PRAGMA table_info(properties)'))->pluck('name')->map(fn ($name) => strtolower($name))->all();

            foreach ($columns as [$column, $type]) {
                if (! in_array(strtolower($column), $existingColumns, true)) {
                    DB::statement("ALTER TABLE properties ADD COLUMN \"{$column}\" {$type} NULL");
                }
            }

            return;
        }

        foreach ($columns as [$column, $type]) {
            DB::statement("ALTER TABLE properties ADD COLUMN IF NOT EXISTS \"{$column}\" {$type} NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        foreach (['bedrooms', 'bathrooms', 'floor_area', 'lot_area', 'frontage', 'stories', 'parking_slots'] as $column) {
            DB::statement("ALTER TABLE properties DROP COLUMN IF EXISTS \"{$column}\"");
        }
    }
};
