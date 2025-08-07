<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TvManager;
use App\Models\Property;

class TvManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all properties
        $properties = Property::all();

        if ($properties->isEmpty()) {
            $this->command->info('No properties found. Please run PropertySeeder first.');
            return;
        }

        $tvManagers = [
            [
                'area_name' => 'Area 1',
                'guest_name' => 'John Smith',
                'image' => null,
                'birth_date' => '1985-03-15',
            ],
            [
                'area_name' => 'Area 2',
                'guest_name' => 'Sarah Johnson',
                'image' => null,
                'birth_date' => '1990-07-22',
            ],
            [
                'area_name' => 'Area 3',
                'guest_name' => 'Michael Brown',
                'image' => null,
                'birth_date' => '1988-11-08',
            ],
            [
                'area_name' => 'Area 4',
                'guest_name' => 'Emily Davis',
                'image' => null,
                'birth_date' => '1992-04-30',
            ],
            [
                'area_name' => 'Area 5',
                'guest_name' => 'David Wilson',
                'image' => null,
                'birth_date' => '1987-09-12',
            ],
            [
                'area_name' => 'Area 6',
                'guest_name' => 'Lisa Anderson',
                'image' => null,
                'birth_date' => '1991-12-25',
            ],
            [
                'area_name' => 'Area 7',
                'guest_name' => 'Robert Taylor',
                'image' => null,
                'birth_date' => '1986-06-18',
            ],
            [
                'area_name' => 'Area 8',
                'guest_name' => 'Jennifer Martinez',
                'image' => null,
                'birth_date' => '1993-01-05',
            ],
            [
                'area_name' => 'Area 9',
                'guest_name' => 'Christopher Garcia',
                'image' => null,
                'birth_date' => '1989-08-14',
            ],
            [
                'area_name' => 'Area 10',
                'guest_name' => 'Amanda Rodriguez',
                'image' => null,
                'birth_date' => '1994-02-28',
            ],
        ];

        foreach ($tvManagers as $tvManager) {
            // Assign random property to each tv manager
            $property = $properties->random();

            TvManager::create([
                'property_id' => $property->id,
                'area_name' => $tvManager['area_name'],
                'guest_name' => $tvManager['guest_name'],
                'image' => $tvManager['image'],
                'birth_date' => $tvManager['birth_date'],
            ]);
        }

        $this->command->info('TvManagerSeeder completed successfully!');
    }
}
