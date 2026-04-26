<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Admin::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => Carbon::now(),
                'status' => 'active',
                'admin_type' => 1, // Jika 1 adalah super admin
                'image' => null,
            ]
        );

        // Pastikan juga ada di tabel users agar bisa login via API mobile (yang pakai guard 'api')
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'username' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => Carbon::now(),
                'status' => 'enable', // HARUS 'enable', bukan 'active'
                'is_banned' => 'no',
            ]
        );
    }
}
