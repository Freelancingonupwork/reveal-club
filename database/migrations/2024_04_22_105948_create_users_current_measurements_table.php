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
        Schema::create('users_current_measurements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('waist')->nullable();
            $table->integer('chest')->nullable();
            $table->integer('arms')->nullable();
            $table->integer('thighs')->nullable();
            $table->integer('hips')->nullable();
            $table->integer('buttocks')->nullable();
            $table->float('weight', 8, 2)->nullable();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->date('last_modified_date')->nullable();
            $table->tinyInteger('isWeight')->default(0)->comment("0: User Measurement Updated 1: Only Weight Updated")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_current_measurements');
    }
};
