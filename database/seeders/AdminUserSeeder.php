<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@absensi.local'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin123!'),
                'role' => 'admin',
            ]
        );
    }
}

