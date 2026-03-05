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
        Schema::table('users_initial_measurements', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn(['arms', 'buttocks']);

            // Change data types
            $table->float('waist', 8, 2)->nullable()->change();
            $table->float('chest', 8, 2)->nullable()->change();
            $table->float('thighs', 8, 2)->nullable()->change();
            $table->float('hips', 8, 2)->nullable()->change();
            $table->float('weight', 8, 2)->nullable()->change();

            // Add new columns
            $table->float('neck', 8, 2)->nullable()->after('weight');
            $table->float('bicep', 8, 2)->nullable()->after('neck');
            $table->float('calfs', 8, 2)->nullable()->after('thighs');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_initial_measurements', function (Blueprint $table) {
            // Revert changes
            $table->integer('arms')->nullable();
            $table->integer('buttocks')->nullable();

            $table->integer('waist')->nullable()->change();
            $table->integer('chest')->nullable()->change();
            $table->integer('thighs')->nullable()->change();
            $table->integer('hips')->nullable()->change();
            $table->integer('weight')->nullable()->change();

            $table->dropColumn(['neck', 'bicep', 'calfs']);
        });
    }
};
