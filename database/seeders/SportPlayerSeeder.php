<?php

namespace Database\Seeders;

use App\Models\SportPlayer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SportPlayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SportPlayer::truncate();

        foreach (range(1, 26) as $playerId) {
            SportPlayer::create([
                'player_id' => $playerId,
                'sport_id' => rand(1, 3), // sport_id secara acak dari 1 sampai 3
            ]);
        }
    }
}
