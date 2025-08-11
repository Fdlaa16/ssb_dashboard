<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Structure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'email' => 'chief@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'email' => 'official@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
            [
                'email' => 'admin@gmail.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ],
        ];

        $structures = [
            [
                'name' => 'Ketua Umum',
                'department' => 'chief',
                'date_of_birth' => now()->format('Y-m-d'),
            ],
            [
                'name' => 'Official',
                'department' => 'official',
                'date_of_birth' => now()->format('Y-m-d'),
            ],
            [
                'name' => 'Admin',
                'department' => 'official',
                'date_of_birth' => now()->format('Y-m-d'),
            ],
        ];

        foreach ($users as $index => $userData) {
            $user = User::create($userData);

            Structure::create([
                'user_id' => $user->id,
                'name' => $structures[$index]['name'],
                'department' => $structures[$index]['department'],
            ]);
        }
    }
}
