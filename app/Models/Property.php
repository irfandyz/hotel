<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Property extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'image',
        'phone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'latitude',
        'longitude',
        'star_rating',
        'total_rooms',
        'hotel_category',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }



    public function tvManagers(): HasMany
    {
        return $this->hasMany(TvManager::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class);
    }

    public function restaurantMenuItems(): HasMany
    {
        return $this->hasMany(RestaurantMenuItem::class);
    }

    public function ownerships(): HasMany
    {
        return $this->hasMany(Ownership::class);
    }
}
