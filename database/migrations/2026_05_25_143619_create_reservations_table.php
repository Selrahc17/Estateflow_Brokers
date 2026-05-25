<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lot_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('broker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reservation_code', 20)->unique();
            $table->string('status', 20)->default('pending'); // pending, confirmed, cancelled, completed
            $table->decimal('total_price', 12, 2)->nullable();
            $table->decimal('down_payment', 12, 2)->nullable();
            $table->string('payment_schedule', 30)->default('monthly'); // monthly, quarterly, annual
            $table->integer('payment_terms_months')->default(60);
            $table->text('notes')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};