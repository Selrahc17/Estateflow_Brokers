<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            return;
        }

        DB::statement('PRAGMA foreign_keys = OFF');
        DB::statement('ALTER TABLE "site_visits" RENAME TO "site_visits_tmp"');

        Schema::create('site_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('broker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('scheduled_at');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        });

        DB::statement('INSERT INTO "site_visits" (id, client_id, property_id, broker_id, inquiry_id, scheduled_at, notes, status, created_at, updated_at) SELECT id, client_id, property_id, broker_id, inquiry_id, scheduled_at, notes, status, created_at, updated_at FROM "site_visits_tmp"');
        Schema::drop('site_visits_tmp');
        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        // The original migration sequence is retained on rollback.
    }
};
