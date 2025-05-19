<?php

namespace Database\Seeders;

use App\Models\ClubPlayer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClubPlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ClubPlayer::truncate();

        foreach (range(1, 26) as $playerId) {
            ClubPlayer::create([
                'player_id' => $playerId,
                'club_id' => rand(1, 3),
            ]);
        }
    }
}
