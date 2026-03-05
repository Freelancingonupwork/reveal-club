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
        Schema::table('challenge_user_statuses', function (Blueprint $table) {
            $table->foreignId('challenge_id')->constrained()->after('user_id')->nullable();
            $table->foreignId('challenge_level_id')->constrained()->nullable();
            $table->integer('completed_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_user_statuses', function (Blueprint $table) {
            $table->dropColumn('challenge_id');
            $table->dropColumn('challenge_level_id');
            $table->dropColumn('completed_day');
        });
    }
};
