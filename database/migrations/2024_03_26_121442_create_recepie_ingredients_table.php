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
        Schema::create('recepie_ingredients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recepie_id')->unsigned()->index()->nullable();
            $table->foreign('recepie_id')->references('id')->on('recipes')->onDelete('cascade');
            $table->unsignedBigInteger('ingredient_id')->unsigned()->index()->nullable();
            $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
            $table->unsignedBigInteger('nutrition_id')->unsigned()->index()->nullable();
            $table->foreign('nutrition_id')->references('id')->on('recipe_nutrition')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('quantity')->nullable();
            $table->string('unit')->nullable();
            $table->integer('category_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recepie_ingredients');
    }
};
