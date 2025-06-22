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
        Schema::create('schedule_trainings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('first_club_id')->nullable();
            $table->unsignedBigInteger('secound_club_id')->nullable();
            $table->unsignedBigInteger('stadium_id')->nullable();
            $table->date('schedule_date')->nullable();
            $table->time('schedule_start_at')->nullable();
            $table->time('schedule_end_at')->nullable();
            $table->string('score')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_trainings');
    }
};
