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
        Schema::create('transitions', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->string('color')->nullable();
            $table->tinyInteger('is_trans_image')->default(0)->nullable();
            $table->text('transition_image')->nullable();
            $table->tinyInteger('is_term_and_cond')->nullable();
            $table->tinyInteger('is_animation')->default(0)->nullable();
            $table->string('animation_text')->nullable();
            $table->tinyInteger('is_paywall')->default(0)->nullable();
            $table->tinyInteger('is_chart')->default(0)->nullable();
            $table->longText('trans_description')->nullable();
            $table->string('button_label')->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->tinyInteger('is_amplitude_track')->default(0)->nullable();
            $table->text('amplitude_tracking_word')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transitions');
    }
};
