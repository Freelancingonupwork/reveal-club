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
        Schema::table('program_levels', function (Blueprint $table) {
             // Remove the old level_points field
            $table->dropColumn(['level_value','level_points']);
            
            // Add new fields for start_range and end_range
            $table->integer('start_range')->nullable()->default(0);
            $table->integer('end_range')->nullable()->default(100);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_levels', function (Blueprint $table) {
            // Add the old level_points field back
            $table->integer('level_points')->nullable();
            $table->integer('level_value')->nullable();

            // Remove the start_range and end_range fields
            $table->dropColumn(['start_range', 'end_range']);
        });
    }
};
