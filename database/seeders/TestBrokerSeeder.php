<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestBrokerSeeder extends Seeder
{
    public function run(): void
    {
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
    }
}