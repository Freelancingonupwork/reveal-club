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
        Schema::create('user_recipes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('meal_type_id')->nullable();
            $table->string('quantity')->nullable()->comment('user added quantity');
            $table->string('kcal')->nullable()->comment('calculated kcal as per 100 g quantity');
            $table->string('protein')->nullable()->comment('calculated protein as per 100 g quantity');
            $table->string('fats')->nullable()->comment('calculated fats as per 100 g quantity');
            $table->string('carbs')->nullable()->comment('calculated carbs as per 100 g quantity');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->string('totalQuantity')->nullable()->comment('total quantity by adding all ingredient Quantity');
            $table->string('totalKcal')->nullable()->comment('total kcal by adding all ingredient kcal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_recipes');
    }
};
