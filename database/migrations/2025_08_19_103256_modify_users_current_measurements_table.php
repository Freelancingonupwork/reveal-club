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
        Schema::table('users_current_measurements', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['arms', 'buttocks']);

            // Change data types
            $table->float('waist', 8, 2)->nullable()->change();
            $table->float('chest', 8, 2)->nullable()->change();
            $table->float('thighs', 8, 2)->nullable()->change();
            $table->float('hips', 8, 2)->nullable()->change();

            // Add new columns
            $table->float('neck', 8, 2)->nullable()->after('weight');
            $table->float('bicep', 8, 2)->nullable()->after('neck');
            $table->float('calfs', 8, 2)->nullable()->after('thighs');

            // `isWeight` already exists, just update default/comment if necessary
            $table->tinyInteger('isWeight')->default(0)->nullable()->comment("0: User Measurement Updated 1: Only Weight Updated")->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_current_measurements', function (Blueprint $table) {
            $table->integer('arms')->nullable();
            $table->integer('buttocks')->nullable();

            $table->integer('waist')->nullable()->change();
            $table->integer('chest')->nullable()->change();
            $table->integer('thighs')->nullable()->change();
            $table->integer('hips')->nullable()->change();

            $table->dropColumn(['neck', 'bicep', 'calfs']);

            // Restore default/comment for isWeight if needed
            $table->tinyInteger('isWeight')->default(0)->nullable()->comment("0: User Measurement Updated 1: Only Weight Updated")->change();
        });
    }
};
