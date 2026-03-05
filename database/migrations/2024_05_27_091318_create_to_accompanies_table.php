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
        Schema::create('to_accompanies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('recipe_id')->index()->nullable();
            $table->foreign('recipe_id')->references('id')->on('recipes')->cascadeOnDelete();
            $table->string('ingredient_name')->nullable();
            $table->string('quantity')->nullable();
            $table->string('unit')->nullable();
            $table->integer('unit_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('to_accompanies');
    }
};
