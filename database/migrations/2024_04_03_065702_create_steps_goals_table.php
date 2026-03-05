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
        Schema::create('steps_goals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->integer('steps_goal')->default(0);
            $table->string('distance')->nullable()->default(0);
            $table->integer('steps_count')->default(0)->nullable();
            $table->integer('steps_completed')->default(0)->nullable();
            $table->integer('steps_remaining')->default(0)->nullable();
            $table->date('goal_date')->nullable();
            $table->time('goal_time')->nullable();
            $table->string('activity_level')->nullable();
            $table->string('activity_factor')->nullable();
            $table->tinyInteger('isCompleted')->default(0)->comment("0: Not Completed, 1: Completed");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('steps_goals');
    }
};
