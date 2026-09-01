<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('broker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('commission_rate', 10, 2)->default(0);
            $table->decimal('broker_share', 10, 2)->default(0);
            $table->decimal('agent_share', 10, 2)->default(0);
            $table->string('payment_schedule', 30)->default('monthly');
            $table->unsignedTinyInteger('payment_day')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_agreements');
    }
};
