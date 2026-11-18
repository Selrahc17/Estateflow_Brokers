<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Which listings a signed-in client has opened, used for "Recommended for you".
     * One row per client and property, updated on each visit.
     */
    public function up(): void
    {
        Schema::create('property_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('visits')->default(1);
            $table->timestamp('last_viewed_at');
            $table->timestamps();

            $table->unique(['user_id', 'property_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_views');
    }
};
