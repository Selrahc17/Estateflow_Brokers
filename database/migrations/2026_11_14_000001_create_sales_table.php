<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One row per sale, for the broker's commission table. Rows are created when an
 * agent marks a listing or lot as Sold, or entered by the broker by hand.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('agent_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('property_id')->nullable()->constrained('properties')->nullOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained('lots')->nullOnDelete();
            $table->date('sold_at');
            $table->decimal('selling_price', 14, 2)->nullable();
            $table->decimal('agent_commission', 14, 2)->nullable();
            $table->decimal('broker_commission', 14, 2)->nullable();
            $table->string('status', 20)->default('pending');
            $table->boolean('show_status_to_agent')->default(false);
            $table->string('source', 10)->default('manual');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['broker_id', 'sold_at']);
            $table->index('agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
