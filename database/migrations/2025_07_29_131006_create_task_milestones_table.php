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
        Schema::create('task_milestones', function (Blueprint $table) {
            $table->id();
            $table->string('milestone_type');
            $table->string('milestone_title')->nullable();
            $table->string('milestone_description')->nullable();
            $table->integer('milestone_count');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_milestones');
    }
};
