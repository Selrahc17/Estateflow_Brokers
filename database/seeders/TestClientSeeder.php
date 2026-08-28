<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestClientSeeder extends Seeder
{
    public function run(): void
    {
        $password = 'password123';

        $user = User::updateOrCreate(
            ['email' => 'client@test.com'],
            [
                'name' => 'Test Client',
                'password' => Hash::make($password),
                'role' => 'client',
                'is_approved' => true,
                'is_active' => true,
            ]
        );

        Client::updateOrCreate(
            ['email' => 'client@test.com'],
            [
                'user_id' => $user->id,
                'first_name' => 'Test',
                'last_name' => 'Client',
                'email' => 'client@test.com',
                'password' => Hash::make($password),
                'status' => 'active',
            ]
        );
    }
}
