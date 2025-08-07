<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            $user = User::factory()->create();
        }

        $properties = [];
        for ($i = 0; $i < 10; $i++) {
            $properties[] = [
                'user_id' => $user->id,
                'name' => 'Property ' . $i,
                'description' => 'Description 1',
                'star_rating' => '5',
                'total_rooms' => '10',
                'hotel_category' => 'hotel',
                'phone' => '1234567890',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'zip' => '10001',
                'country' => 'USA',
                'latitude' => '40.7128',
                'longitude' => '-74.0060',
            ];
        }

        foreach ($properties as $propertyData) {
            Property::create([
                ...$propertyData
            ]);
        }
    }
}
