<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The details agents put in their Facebook posts: payment terms, cash discount,
 * transfer fee, selling stage, nearby landmarks, house models, selling points and
 * buyer requirements. Every column is optional.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('selling_stage', 30)->nullable()->after('status');
            $table->decimal('reservation_fee', 12, 2)->nullable()->after('price');
            $table->decimal('downpayment_percent', 5, 2)->nullable()->after('reservation_fee');
            $table->json('payment_options')->nullable()->after('downpayment_percent');
            $table->string('installment_terms')->nullable()->after('payment_options');
            $table->string('cash_discount_type', 10)->nullable()->after('installment_terms');
            $table->decimal('cash_discount', 12, 2)->nullable()->after('cash_discount_type');
            $table->string('transfer_fee', 20)->nullable()->after('cash_discount');
            $table->decimal('transfer_fee_percent', 5, 2)->nullable()->after('transfer_fee');
            $table->json('selling_points')->nullable()->after('amenities');
            $table->json('landmarks')->nullable()->after('selling_points');
            $table->json('house_models')->nullable()->after('landmarks');
            $table->text('buyer_requirements')->nullable()->after('house_models');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'selling_stage', 'reservation_fee', 'downpayment_percent', 'payment_options', 'installment_terms',
                'cash_discount_type', 'cash_discount', 'transfer_fee', 'transfer_fee_percent',
                'selling_points', 'landmarks', 'house_models', 'buyer_requirements',
            ]);
        });
    }
};
