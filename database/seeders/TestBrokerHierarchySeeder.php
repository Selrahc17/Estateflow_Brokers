<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestBrokerHierarchySeeder extends Seeder
{
    public function run(): void
    {
        $broker = User::updateOrCreate(
            ['email' => 'broker@test.com'],
            [
                'name' => 'Test Broker',
                'password' => Hash::make('password123'),
                'role' => 'broker',
                'is_approved' => true,
                'is_active' => true,
                'broker_id' => null,
            ]
        );

        User::updateOrCreate(
            ['email' => 'agent@test.com'],
            [
                'name' => 'Test Agent',
                'password' => Hash::make('password123'),
                'role' => 'agent',
                'is_approved' => true,
                'is_active' => true,
                'broker_id' => $broker->id,
            ]
        );
    }
}
