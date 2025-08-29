<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Master Admin
        User::updateOrCreate(
            ['email' => env('DEFAULT_MASTER_EMAIL', 'admin@reviewsaas.com')],
            [
                'name' => 'Master Admin',
                'email' => env('DEFAULT_MASTER_EMAIL', 'admin@reviewsaas.com'),
                'password' => Hash::make(env('DEFAULT_MASTER_PASSWORD', 'password123')),
                'role' => 'master_admin',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Create a demo user admin
        User::updateOrCreate(
            ['email' => 'user@demo.com'],
            [
                'name' => 'Demo User',
                'email' => 'user@demo.com',
                'password' => Hash::make('password123'),
                'role' => 'user_admin',
                'phone' => '+91 9876543210',
                'address' => '123 Demo Street',
                'city' => 'Mumbai',
                'state' => 'Maharashtra',
                'pincode' => '400001',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}