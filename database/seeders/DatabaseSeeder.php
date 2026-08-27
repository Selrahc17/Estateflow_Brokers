<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin
        User::create([
            'name'     => 'Super Admin',
            'email'    => 'admin@estateflow.com',
            'password' => 'admin123',
            'role'     => 'admin',
        ]);

        // Broker
        User::create([
            'name'     => 'Broker Name',
            'email'    => 'broker@estateflow.com',
            'password' => 'broker123',
            'role'     => 'agent',
        ]);

        // Test Client (User + Client profile)
        $clientUser = User::create([
            'name'     => 'Juan dela Cruz',
            'email'    => 'client@estateflow.com',
            'password' => 'client123',
            'role'     => 'client',
        ]);

        Client::create([
            'user_id'    => $clientUser->id,
            'first_name' => 'Juan',
            'last_name'  => 'dela Cruz',
            'email'      => 'client@estateflow.com',
            'password'   => 'client123',
            'status'     => 'active',
        ]);
    }
}