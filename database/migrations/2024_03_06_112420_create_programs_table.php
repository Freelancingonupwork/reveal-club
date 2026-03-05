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
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('category_id')->unsigned()->index()->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->string('program_tag')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('objective')->nullable();
            $table->string('slug')->nullable();
            $table->text('program_image')->nullable();
            $table->text('video')->nullable();
            $table->integer('level_id')->nullable();
            $table->string('body_area')->nullable();
            $table->string('duration')->nullable();
            $table->string('frequency')->nullable();
            $table->bigInteger('cardio_id')->unsigned()->index()->nullable();
            $table->foreign('cardio_id')->references('id')->on('cardios')->onDelete('cascade');
            $table->bigInteger('muscle_strength_id')->unsigned()->index()->nullable();
            $table->foreign('muscle_strength_id')->references('id')->on('muscle_strengths')->onDelete('cascade');
            $table->tinyInteger('free_access')->default(0)->comment("0: Not a Free program 1: Free program")->nullable();
            $table->tinyInteger('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('programs');
    }
};
