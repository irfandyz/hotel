<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class RestaurantCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    /**
     * Get the menu items for this category.
     */
    public function menuItems(): BelongsToMany
    {
        return $this->belongsToMany(RestaurantMenuItem::class, 'restaurant_category_menu_items', 'restaurant_category_id', 'restaurant_menu_item_id');
    }
}
