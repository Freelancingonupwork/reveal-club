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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id')->index()->nullable();
            $table->foreign('question_id')->references('id')->on('quizzes')->cascadeOnDelete();
            $table->string('answer_type')->nullable();
            $table->string('answer_format')->nullable();
            $table->string('ques_answers')->nullable();
            $table->string('answer_img')->nullable();
            $table->integer('cardio_and_muscle_id')->default(0)->nullable();
            $table->string('label')->nullable();
            $table->tinyInteger('have_transition')->nullable();
            $table->string('transition_id')->nullable();
            $table->string('transition_logic')->nullable();
            $table->integer('ques_type')->nullable();
            $table->tinyInteger('is_numeric')->default(0)->comment("If answer_type is userInput and if user input should accept only numeric value then it should be yes.")->nullable();
            $table->string('ans_points')->default(0)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
