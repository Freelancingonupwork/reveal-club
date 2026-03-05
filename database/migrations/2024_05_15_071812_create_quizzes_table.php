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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('ques_title')->nullable();
            $table->integer('quiz_group_id')->nullable();
            $table->string('slug')->nullable();
            $table->text('ques_description')->nullable();
            $table->tinyInteger('is_ques_image')->default(0)->nullable();
            $table->text('ques_image')->nullable();
            $table->integer('ques_type')->nullable();
            $table->string('ques_for')->nullable();
            $table->integer('cardio_id')->nullable();
            $table->tinyInteger('is_sales_page')->default(0)->nullable();
            $table->tinyInteger('is_calory_calc')->default(0)->nullable();
            $table->tinyInteger('is_another_ques')->default(0)->nullable();
            $table->integer('ques_id')->nullable();
            $table->tinyInteger('is_have_transition')->default(0)->nullable();
            $table->string('transition_logic')->nullable();
            $table->string('answer_type')->nullable();
            $table->string('answer_format')->nullable();
            $table->string('ques_for_gender')->nullable();
            $table->tinyInteger('have_instruction')->default(0)->nullable();
            $table->text('instruction_message')->nullable();
            $table->integer('quiz_position')->default(0)->nullable();
            $table->tinyInteger('is_google_analytics')->default(0)->nullable();
            $table->text('google_analytic_script')->nullable();
            $table->tinyInteger('is_active')->default(1)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
