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
        Schema::create('nutrition_ingredient_barcodes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nutrition_ingredient_id');
            $table->string('barcode')->unique();
            $table->timestamps();

            $table->foreign('nutrition_ingredient_id')
                ->references('id')
                ->on('nutrition_ingredients')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nutrition_ingredient_barcodes', function (Blueprint $table) {
            Schema::dropIfExists('nutrition_ingredient_barcodes');
        });
    }
};
