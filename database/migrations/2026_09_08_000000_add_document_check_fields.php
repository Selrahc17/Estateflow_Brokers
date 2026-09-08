<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('check_status', 20)->nullable()->after('status');
            $table->unsignedTinyInteger('check_score')->nullable()->after('check_status');
            $table->json('check_findings')->nullable()->after('check_score');
            $table->timestamp('checked_at')->nullable()->after('check_findings');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['check_status', 'check_score', 'check_findings', 'checked_at']);
        });
    }
};
