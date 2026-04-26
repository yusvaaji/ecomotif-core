<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mengecek apakah admin sudah ada
        $admin = Admin::where('email', 'admin@example.com')->first();

        if (!$admin) {
            Admin::create([
                'name' => 'Super Admin',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => Carbon::now(),
                'status' => 'active',
                'admin_type' => 1, // Jika 1 adalah super admin
                'image' => null,
            ]);
        }
    }
}
