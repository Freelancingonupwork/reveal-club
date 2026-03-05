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
        Schema::create('unsubscription_flow_questions', function (Blueprint $table) {
            $table->id();
            $table->string('screen_title')->nullable();
            $table->tinyInteger('show_title')->default(1)->nullable();
            $table->string('slug')->nullable();
            $table->tinyInteger('is_screen_image')->default(0)->nullable();
            $table->text('screen_image')->nullable();
            $table->text('screen_description')->nullable();
            $table->text('button_text')->nullable();
            $table->tinyInteger('show_default_button')->default(1)->nullable();
            $table->tinyInteger('is_multiple_button')->default(0)->nullable();
            $table->json('multiple_buttons_value')->nullable();
            $table->tinyInteger('have_offer')->nullable();
            $table->integer('offer_in_per')->nullable();
            $table->integer('screen_position')->nullable();
            $table->tinyInteger('screen_type')->default(0)->nullable()->comment('0-feedback, 1-transition');
            $table->string('feedback_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unsubscription_flow_questions');
    }
};
