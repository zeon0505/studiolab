<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Item;
use App\Models\DailyAssignment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Admin Account
        $admin = User::updateOrCreate(
            ['email' => 'admin@staimas.com'],
            [
                'name' => 'Administrator Studio & Lab',
                'no_wa' => '081234567890',
                'password' => Hash::make('adminstamas'),
            ]
        );

        // Akun Admin Yoga
        $adminYoga = User::updateOrCreate(
            ['email' => 'yoga@staimas.com'],
            [
                'name' => 'Yoga',
                'no_wa' => '081234567899',
                'password' => Hash::make('adminyoga'),
            ]
        );
    }
}
