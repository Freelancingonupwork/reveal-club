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
        Schema::create('user_progress_images', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('before_image_id')->nullable();
            $table->unsignedBigInteger('after_image_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('before_image_id')->references('id')->on('users_appearance_infos')->onDelete('cascade');
            $table->foreign('after_image_id')->references('id')->on('users_appearance_infos')->onDelete('cascade');
            
            // Ensure a user can only have one active progress image pair
            $table->unique(['user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_progress_images');
    }
};
