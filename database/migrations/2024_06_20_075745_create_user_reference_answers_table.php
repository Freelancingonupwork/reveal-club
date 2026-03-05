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
        Schema::create('user_reference_answers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_answered_id')->unsigned()->index()->nullable();
            $table->integer('question_id')->nullable();
            $table->bigInteger('user_answer_id')->unsigned()->index()->nullable();
            $table->integer('quiz_group_id')->nullable();
            $table->string('answer_type')->nullable();
            $table->string('key')->nullable();
            $table->string('value')->nullable();
            $table->string('answer')->nullable();
            $table->tinyInteger('email_marketing')->default(0)->nullable();
            $table->string('session_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_reference_answers');
    }
};
