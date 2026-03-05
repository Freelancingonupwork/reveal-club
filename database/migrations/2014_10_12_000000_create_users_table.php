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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->nullable()->comment('0: Administration, 2: Customer');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('mobile')->nullable();
            $table->string('gender')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('height')->nullable()->default(0)->comment('in cm');
            $table->string('avatar')->nullable();
            $table->string('password')->nullable();
            $table->string('login_type')->nullable()->comment("This field contains user logged in using which method. It may be Apple, Google, Facebook, or simply using email");
            $table->string('device_type')->nullable();
            $table->string('device_token')->nullable();
            $table->string('login_key')->nullable();
            $table->tinyInteger('status')->default(1)->nullable();
            $table->tinyInteger('isQuestionsAttempted')->default(0)->comment('0: Not Attempted 1: Attempted')->nullable();
            $table->tinyInteger('isSubscribedUser')->default(0)->comment('0: Not Subscribed 1: Subscribed')->nullable();
            $table->tinyInteger('iosSubscribedUser')->default(0)->comment('0: Not Subscribed 1: Subscribed')->nullable();
            $table->string('referral_source')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->text('address')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('company')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
