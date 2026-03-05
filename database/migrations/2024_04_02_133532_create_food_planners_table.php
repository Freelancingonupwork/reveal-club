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
        Schema::create('food_planners', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('recipe_or_ingredient_id')->nullable();
            $table->string('meal_type')->nullable();
            $table->date('date')->nullable();
            $table->integer('potion')->default(0)->nullable();
            $table->string('kcal')->default(0)->nullable();
            $table->string('carbs')->default(0)->nullable();
            $table->string('fats')->default(0)->nullable();
            $table->string('proteins')->default(0)->nullable();
            $table->integer('no_of_servings')->nullable()->default(0);
            $table->tinyInteger('is_ingredient')->default(0)->comment("0: Not an Ingredient, 1: It is an Ingredient, 2: Use for water")->nullable();
            $table->integer('water_consume')->default(0)->comment("In ml")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_planners');
    }
};
