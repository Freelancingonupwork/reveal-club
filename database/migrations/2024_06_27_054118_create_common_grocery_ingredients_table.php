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
        Schema::create('common_grocery_ingredients', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('recipe_id')->unsigned()->index()->nullable();
            $table->foreign('recipe_id')->references('id')->on('recipes')->OnDelete('cascade');
            $table->bigInteger('recipe_ingredient_id')->nullable();
            $table->integer('ingredient_category_id')->nullable();
            $table->integer('ingredient_nutrition_id')->nullable();
            $table->bigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('ingredient_name')->nullable();
            $table->integer('ingredient_quantity')->nullable();
            $table->string('ingredient_unit')->nullable();
            $table->integer('no_of_person')->nullable();
            $table->tinyInteger('isPurchased')->default("0")->comment("0:Not Purchased 1:Purchased")->nullable();
            $table->tinyInteger('is_personalised')->defaul(0)->comment("0:From Recipe Ingredients 1:From Personalised")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('common_grocery_ingredients');
    }
};
