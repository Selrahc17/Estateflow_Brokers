<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $tableName = 'inquiries';
            $temporaryTableName = 'inquiries_tmp';

            DB::statement("ALTER TABLE \"{$tableName}\" RENAME TO \"{$temporaryTableName}\"");

            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('property_id')->constrained()->onDelete('cascade');
                $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('lot_id')->nullable()->constrained()->onDelete('cascade');
                $table->text('message')->nullable();
                $table->string('status')->default('new');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->timestamps();
            });

            DB::statement("INSERT INTO \"{$tableName}\" (id, user_id, property_id, broker_id, lot_id, message, status, phone, email, created_at, updated_at) SELECT id, user_id, property_id, broker_id, lot_id, message, status, phone, email, created_at, updated_at FROM \"{$temporaryTableName}\"");
            Schema::drop($temporaryTableName);

            return;
        }

        Schema::table('inquiries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            $tableName = 'inquiries';
            $temporaryTableName = 'inquiries_tmp';

            DB::statement("ALTER TABLE \"{$tableName}\" RENAME TO \"{$temporaryTableName}\"");

            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('property_id')->constrained()->onDelete('cascade');
                $table->foreignId('broker_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('lot_id')->nullable()->constrained()->onDelete('cascade');
                $table->text('message')->nullable();
                $table->string('status')->default('new');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->timestamps();
            });

            DB::statement("INSERT INTO \"{$tableName}\" (id, user_id, property_id, broker_id, lot_id, message, status, phone, email, created_at, updated_at) SELECT id, user_id, property_id, broker_id, lot_id, message, status, phone, email, created_at, updated_at FROM \"{$temporaryTableName}\"");
            Schema::drop($temporaryTableName);

            return;
        }

        Schema::table('inquiries', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable(false)->change();
        });
    }
};
