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
        Schema::create('user_feedback', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('screen_id');
            $table->string('feedback');
            $table->timestamps();

            // $table->id();
            // $table->unsignedBigInteger('user_id')->index();
            // $table->foreign('user_id')->references('id')->on('cancel_subscriptions')->cascadeOnDelete();
            // $table->unsignedBigInteger('screen_id')->index();
            // $table->foreign('screen_id')->references('id')->on('users')->cascadeOnDelete();
            // $table->string('feedback');
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_feedback');
    }
};
