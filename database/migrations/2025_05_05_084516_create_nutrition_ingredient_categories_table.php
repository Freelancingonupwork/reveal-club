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
        Schema::create('nutrition_ingredient_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('image_url')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        
        Schema::create('nutrition_ingredient_category_portion_sizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nutrition_ingredient_category_id');
            $table->foreign('nutrition_ingredient_category_id', 'nic_ps_category_id_fk')
                  ->references('id')
                  ->on('nutrition_ingredient_categories')
                  ->onDelete('cascade');
            $table->string('name');
            $table->decimal('quantity', 10, 2);
            $table->string('units');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrition_ingredient_category_portion_sizes');
        Schema::dropIfExists('nutrition_ingredient_categories');
    }
};
