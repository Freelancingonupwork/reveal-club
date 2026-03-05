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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->string('title', 1000)->nullable();
            $table->longText('description')->nullable();
            $table->string('slug', 1000)->nullable();
            $table->string('video_title', 1000)->nullable();
            $table->longText('video_description')->nullable();
            $table->text('video_link')->nullable();
            $table->string('lesson_question', 1000)->nullable();
            $table->string('answers', 1000)->nullable();
            $table->string('task_title', 1000)->nullable();
            $table->string('task_content', 1000)->nullable();
            $table->tinyInteger('isFeature')->nullable()->default(0);
            $table->string('feature_name', 1000)->nullable();
            $table->string('feature_title', 1000)->nullable();
            $table->longText('feature_desc')->nullable();
            $table->string('feature_image', 1000)->nullable();
            $table->tinyInteger('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
