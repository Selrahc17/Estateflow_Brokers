<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('properties')->where('type', 'Farm')->update(['type' => 'Farm Lot']);
    }

    public function down(): void
    {
        DB::table('properties')->where('type', 'Farm Lot')->update(['type' => 'Farm']);
    }
};
