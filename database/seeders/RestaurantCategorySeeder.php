<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RestaurantCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Main Course',
            'Appetizer',
            'Dessert',
            'Beverage',
            'Snack',
            'Breakfast',
            'Lunch',
            'Dinner',
            'Salad',
            'Soup',
            'Side Dish',
            'Kids Menu',
            'Vegetarian',
            'Vegan',
            'Seafood',
            'Grill',
            'Pasta',
            'Pizza',
            'Asian',
            'Western',
            'Other',
        ];

        foreach ($categories as $category) {
            DB::table('restaurant_categories')->insert([
                'name' => $category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
