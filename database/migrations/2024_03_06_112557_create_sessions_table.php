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
        Schema::create('sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('title_for_frontend')->nullable();
            $table->string('exercise')->nullable();
            $table->string('slug')->nullable();
            $table->string('difficulty_level')->nullable();
            $table->string('body_area')->nullable();
            $table->time('time')->nullable();
            $table->string('video_link')->nullable();
            $table->text('summary')->nullable();
            $table->string('calories')->nullable();
            $table->string('session_type')->nullable();
            $table->tinyInteger('status')->nullable();
            $table->tinyInteger('free_access')->default(0)->comment("0: Not a Free Session 1: Free Session")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
