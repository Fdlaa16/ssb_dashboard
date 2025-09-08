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
        Schema::create('schedule_match_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_match_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['h_minus_7', 'h_minus_1', 'day_h']);
            $table->boolean('is_sent')->default(false);
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['schedule_match_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_match_notifications');
    }
};
