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
        Schema::create('users_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->bigInteger('plan_id')->nullable();
            $table->bigInteger('next_plan_id')->nullable();
            $table->string('customer_id');
            $table->string('subscription_id');
            $table->integer('amount');
            $table->integer('billing_cycle')->comment("in Month");
            $table->string('status');
            $table->tinyInteger('is_refunded')->default(0)->nullable();
            $table->tinyInteger('is_applied_for_trial')->default(0)->nullable();
            $table->tinyInteger('is_applied_for_cancel')->default(0)->nullable();
            $table->tinyInteger('is_applied_for_discount')->default(0)->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_subscriptions');
    }
};
