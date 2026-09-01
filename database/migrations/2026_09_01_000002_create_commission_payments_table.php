<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commission_agreement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained()->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->decimal('amount_due', 12, 2)->default(0);
            $table->decimal('agent_amount', 12, 2)->default(0);
            $table->decimal('broker_amount', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->string('payment_status', 30)->default('pending');
            $table->string('proof_path')->nullable();
            $table->text('payment_message')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_payments');
    }
};
