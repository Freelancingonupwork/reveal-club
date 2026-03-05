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
        Schema::create('recipe_nutrition', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id')->index()->nullable();
            $table->foreign('recipe_id')->references('id')->on('recipes')->onDelete('cascade');
            $table->string('kcal')->nullable();
            $table->string('protien')->nullable();
            $table->string('fat')->nullable();
            $table->string('carbs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_nutrition');
    }
};
