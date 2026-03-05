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
        Schema::create('exercise_of_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_day_id')->constrained();
            $table->foreignId('exercise_id')->constrained();
            $table->string('exercise_type')->nullable();
            $table->integer('duration')->nullable()->comment('in_seconds');
            $table->integer('no_of_repetition')->nullable();
            $table->integer('rest_period')->nullable()->comment('in_seconds');;
            $table->integer('order')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_of_challenges');
    }
};
