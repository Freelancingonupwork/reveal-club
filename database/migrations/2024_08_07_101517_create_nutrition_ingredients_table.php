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
        Schema::create('nutrition_ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 1000);
            $table->string('kcal', 1000);
            $table->string('protein', 1000);
            $table->string('fats', 1000);
            $table->string('carbs', 1000);
            $table->string('sugar', 1000);
            $table->string('salt', 1000);
            $table->string('image_url', 1000);
            $table->string('small_image_url', 1000);
            $table->string('slug', 1000);
            $table->string('category', 500)->default('Unassigned');
            $table->string('subcategory', 500)->default('Unassigned');
            $table->tinyInteger('mark_as_reviewed')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrition_ingredients');
    }
};
