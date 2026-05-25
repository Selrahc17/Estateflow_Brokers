<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('broker_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_code', 20)->unique();
            $table->decimal('amount', 12, 2);
            $table->string('payment_type', 30)->default('monthly'); // down_payment, monthly, full, other
            $table->string('payment_method', 30)->nullable(); // cash, bank_transfer, GCash, Maya, check
            $table->string('reference_number')->nullable();
            $table->string('status', 20)->default('pending'); // pending, verified, failed, refunded
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};