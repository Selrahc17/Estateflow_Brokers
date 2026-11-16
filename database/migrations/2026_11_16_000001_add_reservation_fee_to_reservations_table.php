<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The amount a buyer paid to reserve. Financing (downpayment, schedule, terms)
 * is handled by the developer now, so those columns stay but are no longer used.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->decimal('reservation_fee', 12, 2)->nullable()->after('total_price');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('reservation_fee');
        });
    }
};
