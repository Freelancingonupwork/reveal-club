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
        Schema::create('community_post_comment_reports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('comment_or_reply_id');
            $table->unsignedBigInteger('user_id');
            $table->enum('is_comment_or_reply', ['comment', 'reply'])->default('comment');
            $table->string('reason');
            $table->text('feedback')->nullable();
            $table->enum('mark_as_solved', ['0', '1'])->default('0')->comment('0 = Unsolved, 1 = Solved');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('community_post_comment_reports');
    }
};
