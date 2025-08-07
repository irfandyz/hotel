<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;

class HotelSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            $user = User::factory()->create();
        }

        $hotels = [
            [
                'name' => 'Hotel Grand Jakarta',
                'description' => 'Hotel bintang 5 di pusat Jakarta dengan fasilitas lengkap',
                'phone' => '+62 21 555-0123',
                'address' => 'Jl. Sudirman No. 123',
                'city' => 'Jakarta',
                'state' => 'DKI Jakarta',
                'zip' => '12190',
                'country' => 'Indonesia',
                'latitude' => '-6.2088',
                'longitude' => '106.8456',
                'star_rating' => 5,
                'total_rooms' => 200,
                'hotel_category' => 'luxury',
            ],
            [
                'name' => 'Hotel Central Bandung',
                'description' => 'Hotel nyaman di pusat kota Bandung dengan view pegunungan',
                'phone' => '+62 22 555-0456',
                'address' => 'Jl. Asia Afrika No. 45',
                'city' => 'Bandung',
                'state' => 'Jawa Barat',
                'zip' => '40111',
                'country' => 'Indonesia',
                'latitude' => '-6.9175',
                'longitude' => '107.6191',
                'star_rating' => 4,
                'total_rooms' => 150,
                'hotel_category' => 'mid-range',
            ],
            [
                'name' => 'Hotel Budget Surabaya',
                'description' => 'Hotel ekonomis dengan fasilitas standar di Surabaya',
                'phone' => '+62 31 555-0789',
                'address' => 'Jl. Tunjungan No. 67',
                'city' => 'Surabaya',
                'state' => 'Jawa Timur',
                'zip' => '60261',
                'country' => 'Indonesia',
                'latitude' => '-7.2575',
                'longitude' => '112.7521',
                'star_rating' => 2,
                'total_rooms' => 80,
                'hotel_category' => 'budget',
            ],
        ];

        foreach ($hotels as $hotelData) {
            Property::create([
                'user_id' => $user->id,
                ...$hotelData
            ]);
        }
    }
}
