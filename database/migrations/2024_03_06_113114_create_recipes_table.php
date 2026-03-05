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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('tags')->nullable();
            $table->string('meal_type_id')->nullable();
            $table->string('slug')->nullable();
            $table->binary('picture')->nullable();
            $table->time('cooking_time')->nullable();
            $table->time('overall_time')->nullable();
            $table->text('about')->nullable();
            $table->text('prep_video')->nullable();
            $table->tinyInteger('is_person')->default(0)->comment("0: No of Persons Not Available 1: No of Persons Available.")->nullable();
            $table->integer('no_of_person')->default(1)->nullable();
            $table->tinyInteger('free_access')->default(0)->comment("0: Not a Free Recipe 1: Free Recipe")->nullable();
            $table->tinyInteger('special_repipe')->default(0)->comment("0: Not a Special Recipe 1: Special Recipe")->nullable();
            $table->tinyInteger('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
