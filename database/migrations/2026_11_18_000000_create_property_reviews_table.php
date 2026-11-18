<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamp('hidden_at')->nullable();
            $table->timestamps();

            // One review per client per property; they edit it instead of adding more.
            $table->unique(['property_id', 'user_id']);
            $table->index(['property_id', 'hidden_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_reviews');
    }
};
