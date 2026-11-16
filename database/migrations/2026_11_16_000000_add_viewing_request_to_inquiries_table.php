<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A buyer can send a plain inquiry or ask to view the property at a preferred
 * date and time. Existing inquiries stay plain inquiries.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->string('type', 20)->default('inquiry')->after('broker_id');
            $table->timestamp('preferred_viewing_at')->nullable()->after('message');
        });
    }

    public function down(): void
    {
        Schema::table('inquiries', function (Blueprint $table) {
            $table->dropColumn(['type', 'preferred_viewing_at']);
        });
    }
};
