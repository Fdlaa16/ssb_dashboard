<?php

namespace Database\Seeders;

use App\Models\Club;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClubSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Persib',
            ],
            [
                'name' => 'Persija',
            ],
            [
                'name' => 'Arema FC',
            ],
        ];

        foreach ($data as $item) {
            Club::create([
                'name' => $item['name'],
            ]);
        }
    }
}
