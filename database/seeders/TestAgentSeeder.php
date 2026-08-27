<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestAgentSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'agent@test.com'],
            [
                'name' => 'Test Agent',
                'password' => Hash::make('password123'),
                'role' => 'agent',
                'is_approved' => true,
                'is_active' => true,
            ]
        );
    }
}
