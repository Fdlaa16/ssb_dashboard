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
            $table->unsignedBigInteger('club_id')->nullable();
            $table->string('total')->nullable();
            $table->string('win')->nullable();
            $table->string('draw')->nullable();
            $table->string('lose')->nullable();
            $table->string('goal_in')->nullable();
            $table->string('goal_conceded')->nullable();
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
