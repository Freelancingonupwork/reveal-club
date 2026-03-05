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
        Schema::create('users_initial_measurements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('waist')->nullable();
            $table->integer('chest')->nullable();
            $table->integer('arms')->nullable();
            $table->integer('thighs')->nullable();
            $table->integer('hips')->nullable();
            $table->integer('buttocks')->nullable();
            $table->integer('weight')->nullable();
            $table->integer('age')->nullable();
            $table->string('gender')->nullable();
            $table->date('added_date')->nullable();
            $table->date('last_modified_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users_initial_measurements');
    }
};
