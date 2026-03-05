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
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('title_for_frontend')->nullable();
            $table->string('slug')->nullable();
            $table->text('instructions')->nullable();
            $table->string('exercise_form')->nullable();
            $table->time('duration')->nullable();
            $table->integer('no_of_repetition')->nullable();
            $table->integer('range_of_repetition')->nullable();
            $table->time('rest_period')->nullable();;
            $table->text('video')->comment("Youtube Link")->nullable();
            $table->string('body_zone')->nullable();
            $table->string('exercise_type')->nullable();
            $table->text('gif')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
