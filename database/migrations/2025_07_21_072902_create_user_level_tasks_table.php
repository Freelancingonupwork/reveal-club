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
        Schema::create('user_level_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_level_id');
            $table->string('task_type');
            $table->bigInteger('total_count');
            $table->time('duration')->nullable();
            $table->timestamps();

            $table->foreign('user_level_id')->references('id')->on('user_levels')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_level_tasks');
    }
};
