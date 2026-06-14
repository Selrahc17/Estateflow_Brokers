<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create test broker if not exists
        $broker = User::where('email', 'broker@estateflow.com')->first();
        if (!$broker) {
            $broker = User::firstOrCreate(
                ['email' => 'broker@estateflow.com'],
                ['name' => 'Test Broker']
            );
        }

        // Create test client
        Client::firstOrCreate(
            ['email' => 'testclient@test.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '123-456-7890',
                'password' => bcrypt('password123'),
                'status' => 'active'
            ]
        );

        // Create test property
        Property::firstOrCreate(
            ['name' => 'Test Property'],
            [
                'slug' => 'test-property',
                'address' => '123 Main St',
                'city' => 'Test City',
                'province' => 'TC',
                'description' => 'A beautiful test property for testing purposes.',
                'type' => 'house',
                'price' => 1500000,
                'status' => 'available',
                'broker_id' => $broker->id,
            ]
        );
    }
}