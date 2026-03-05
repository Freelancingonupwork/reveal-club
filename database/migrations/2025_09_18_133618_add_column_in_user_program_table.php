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
        Schema::table('user_programs', function (Blueprint $table) {
            $table->integer('program_type')->default(0)->comment('0: both, 1: cardio, 2: muscle');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_programs', function (Blueprint $table) {
            $table->dropColumn('program_type');
        });
    }
};
