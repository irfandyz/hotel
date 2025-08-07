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

        $properties = [
            [
                'name' => 'Hotel Grand Jakarta',
                'type' => 'hotel',
                'description' => 'Hotel bintang 5 di pusat Jakarta dengan fasilitas lengkap',
                'phone' => '+62 21 555-0123',
                'address' => 'Jl. Sudirman No. 123',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'zip' => '12190',
                'country' => 'Indonesia',
                'latitude' => '-6.2088',
                'longitude' => '106.8456',
            ],
            [
                'name' => 'Restaurant Seafood Delight',
                'type' => 'restaurant',
                'description' => 'Restaurant seafood terbaik dengan view laut',
                'phone' => '+62 21 555-0456',
                'address' => 'Jl. Pantai Indah No. 45',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'zip' => '14450',
                'country' => 'Indonesia',
                'latitude' => '-6.1145',
                'longitude' => '106.8456',
            ],
            [
                'name' => 'Cafe Aroma',
                'type' => 'cafe',
                'description' => 'Cafe cozy dengan kopi premium dan makanan ringan',
                'phone' => '+62 21 555-0789',
                'address' => 'Jl. Kemang Raya No. 67',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'zip' => '12730',
                'country' => 'Indonesia',
                'latitude' => '-6.2604',
                'longitude' => '106.8124',
            ],
        ];

        foreach ($properties as $propertyData) {
            Property::create([
                'user_id' => $user->id,
                ...$propertyData
            ]);
        }
    }
}
