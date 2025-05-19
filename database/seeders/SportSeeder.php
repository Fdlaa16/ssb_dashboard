<?php

namespace Database\Seeders;

use App\Models\Sport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Sepak Bola',
            ],
            [
                'name' => 'Lari',
            ],
            [
                'name' => 'Renang',
            ],
        ];

        foreach ($data as $item) {
            Sport::create([
                'name' => $item['name'],
            ]);
        }
    }
}
