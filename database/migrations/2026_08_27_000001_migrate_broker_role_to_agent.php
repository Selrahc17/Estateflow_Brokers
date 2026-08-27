<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('role', 'broker')
            ->update(['role' => 'agent']);
    }

    public function down(): void
    {
        DB::table('users')
            ->where('role', 'agent')
            ->update(['role' => 'broker']);
    }
};
