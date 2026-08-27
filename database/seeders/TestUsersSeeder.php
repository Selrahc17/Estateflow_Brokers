<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Admin accounts
        User::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_approved' => true,
                'is_active' => true
            ]
        );
        
        User::updateOrCreate(
            ['email' => 'admin@estateflow.com'],
            [
                'name' => 'EstateFlow Admin',
                'email' => 'admin@estateflow.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'is_approved' => true,
                'is_active' => true
            ]
        );

        // Broker accounts
        User::updateOrCreate(
            ['email' => 'broker@test.com'],
            [
                'name' => 'Test Broker',
                'email' => 'broker@test.com',
                'password' => Hash::make('password123'),
                'role' => 'agent',
                'is_approved' => true,
                'is_active' => true
            ]
        );
        
        User::updateOrCreate(
            ['email' => 'broker@estateflow.com'],
            [
                'name' => 'EstateFlow Broker',
                'email' => 'broker@estateflow.com',
                'password' => Hash::make('broker123'),
                'role' => 'agent',
                'is_approved' => true,
                'is_active' => true
            ]
        );

        // Client account
        User::updateOrCreate(
            ['email' => 'client@estateflow.com'],
            [
                'name' => 'EstateFlow Client',
                'email' => 'client@estateflow.com',
                'password' => Hash::make('client123'),
                'role' => 'client',
                'is_approved' => true,
                'is_active' => true
            ]
        );
    }
}