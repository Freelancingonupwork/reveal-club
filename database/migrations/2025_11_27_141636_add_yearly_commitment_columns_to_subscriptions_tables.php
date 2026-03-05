<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users_subscriptions', function (Blueprint $table) {
            $table->tinyInteger('is_yearly_commitment')->default(0);
            $table->tinyInteger('is_cancellation_locked')->default(0);
            $table->integer('subscription_year_cycle')->nullable();
            $table->dateTime('lockDate')->nullable();
        });

        Schema::table('subscription_histories', function (Blueprint $table) {
            $table->tinyInteger('is_yearly_commitment')->default(0);
            $table->tinyInteger('is_cancellation_locked')->default(0);
            $table->integer('subscription_year_cycle')->nullable();
            $table->dateTime('lockDate')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'is_yearly_commitment',
                'is_cancellation_locked',
                'subscription_year_cycle',
                'lockDate'
            ]);
        });

        Schema::table('subscription_histories', function (Blueprint $table) {
            $table->dropColumn([
                'is_yearly_commitment',
                'is_cancellation_locked',
                'subscription_year_cycle',
                'lockDate'
            ]);
        });
    }
};
