<?php

namespace Database\Seeders;

use App\Models\Player;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'email' => 'user2@gmail.com',
            'password' => Hash::make('password'), // Gunakan bcrypt / hash
        ]);

        // Buat player terkait user
        Player::create([
            'user_id' => $user->id,
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'height' => 175,
            'weight' => 68,
            'back_number' => 10,
            'position' => 'Forward',
            'is_captain' => true,
            'category' => 'U-18',
            'status' => 1,
        ]);
    }
}
