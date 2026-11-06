<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('prc_id_expires_at')->nullable()->after('license_number');
            $table->string('dhsud_registration_number', 100)->nullable()->after('prc_id_expires_at');
            $table->date('dhsud_registration_expires_at')->nullable()->after('dhsud_registration_number');
            $table->string('tin', 20)->nullable()->after('dhsud_registration_expires_at');
            $table->timestamp('privacy_consented_at')->nullable()->after('tin');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'prc_id_expires_at',
                'dhsud_registration_number',
                'dhsud_registration_expires_at',
                'tin',
                'privacy_consented_at',
            ]);
        });
    }
};
