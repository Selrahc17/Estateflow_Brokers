<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Each agent keeps their own record of a buyer, so the same email may appear once per agent
 * instead of once in the whole system.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_email_unique');
            $table->unique(['email', 'broker_id'], 'clients_email_broker_unique');
        });
    }

    public function down(): void
    {
        // Fails if a buyer is already a client of more than one agent, which is the safe outcome.
        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique('clients_email_broker_unique');
            $table->unique('email');
        });
    }
};
