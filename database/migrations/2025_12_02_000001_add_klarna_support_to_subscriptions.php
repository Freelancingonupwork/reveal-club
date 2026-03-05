<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds columns to support Klarna recurring payments:
     * - payment_method_type: Tracks if subscription uses 'klarna' or 'card'
     * - invoice_id: Prevents duplicate webhook processing (idempotency)
     */
    public function up(): void
    {
        // Add to users_subscriptions table
        Schema::table('users_subscriptions', function (Blueprint $table) {
            // Track payment method type (klarna vs card)
            $table->string('payment_method_type', 50)
                ->default('card')
                ->after('subscription_id')
                ->comment('Payment method: card, klarna, etc');
                
            // Store Stripe Invoice ID for idempotency
            $table->string('invoice_id', 255)
                ->nullable()
                ->after('payment_method_type')
                ->comment('Latest Stripe Invoice ID');
        });
        
        // Add to subscription_histories table for tracking
        Schema::table('subscription_histories', function (Blueprint $table) {
            // Track payment method type (klarna vs card)
            $table->string('payment_method_type', 50)
                ->default('card')
                ->after('subscription_id')
                ->comment('Payment method: card, klarna, etc');
                
            // Store invoice ID to prevent duplicate processing
            $table->string('invoice_id', 255)
                ->nullable()
                ->after('payment_method_type')
                ->comment('Stripe Invoice ID');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['payment_method_type', 'invoice_id']);
        });

        Schema::table('subscription_histories', function (Blueprint $table) {
            $table->dropColumn(['invoice_id', 'payment_method_type']);
        });

    }
};
