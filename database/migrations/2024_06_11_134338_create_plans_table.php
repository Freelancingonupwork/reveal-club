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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->string('price');
            $table->string('discprice');
            $table->string('total_price');
            $table->string('total_disc_price');
            $table->string('offer_label')->nullable();
            $table->tinyInteger('plan_type')->comment('0: Monthly, 1: Quartarly, 2: Yearly, 3: Bi-Yearly')->nullable();
            $table->string('dayscount')->nullable();
            $table->tinyInteger('freetrial')->default(1)->nullable();
            $table->integer('trialdays')->nullable();
            $table->text('image')->nullable();
            $table->tinyInteger('for_klarna')->default(0)->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
