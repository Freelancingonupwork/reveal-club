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
        Schema::create('user_session_statuses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('program_id')->nullable();
            $table->integer('session_id')->nullable();
            $table->string('session_week')->nullable();
            $table->integer('program_session_id')->nullable();
            $table->tinyInteger('user_session_status')->default(0)->nullable()->comment("0:Pending 1:Completed");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_session_statuses');
    }
};
