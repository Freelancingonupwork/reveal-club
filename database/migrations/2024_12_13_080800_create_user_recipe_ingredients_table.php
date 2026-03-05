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
        Schema::create('user_recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_recipes_id')->unsigned()->index()->nullable();
            $table->foreign('user_recipes_id')->references('id')->on('user_recipes')->onDelete('cascade');
            $table->bigInteger('ingredient_id')->unsigned()->index()->nullable();
            $table->foreign('ingredient_id')->references('id')->on('nutrition_ingredients')->onDelete('cascade');
            $table->string('quantity')->nullable();
            $table->string('kcal')->nullable();
            $table->string('protein')->nullable();
            $table->string('fats')->nullable();
            $table->string('carbs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_recipe_ingredients');
    }
};
