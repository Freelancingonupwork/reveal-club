<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('nutrition_ingredient_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('nutrition_ingredient_id')->constrained('nutrition_ingredients')->onDelete('cascade');
            $table->string('size_key'); // Store size key
            $table->integer('value'); // Store value in grams
            $table->string('units')->default('grams'); // Store units, default to grams
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('nutrition_ingredient_units');
    }
};
