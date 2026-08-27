<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->string('attachment')->nullable()->after('message');
            $table->timestamp('delivered_at')->nullable()->after('is_read');
            $table->timestamp('seen_at')->nullable()->after('delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['attachment', 'delivered_at', 'seen_at']);
        });
    }
};
