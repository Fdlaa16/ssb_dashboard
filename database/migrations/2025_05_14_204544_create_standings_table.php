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
        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_match_id')->nullable();
            $table->unsignedBigInteger('schedule_training_id')->nullable();
            $table->string('win')->nullable();
            $table->string('draw')->nullable();
            $table->string('lose')->nullable();
            $table->string('goals_scored')->nullable();
            $table->string('goals_conceded')->nullable();
            $table->string('goal_difference')->nullable();
            $table->string('points')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('standings');
    }
};
