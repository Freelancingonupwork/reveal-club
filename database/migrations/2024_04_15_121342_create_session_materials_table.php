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
        Schema::create('session_materials', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('session_id')->unsigned()->index()->nullable();
            $table->foreign('session_id')->references('id')->on('sessions')->onDelete('cascade');
            $table->string('material_name')->nullable();
            $table->text('material_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_materials');
    }
};
