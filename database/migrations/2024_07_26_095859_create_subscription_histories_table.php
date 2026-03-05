<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('subscription_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->bigInteger('plan_id')->nullable();
            $table->string('customer_id');
            $table->string('subscription_id');
            $table->integer('amount');
            $table->string('status');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->tinyInteger('taken_trial');
            $table->tinyInteger('taken_discount');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_histories');
    }
};