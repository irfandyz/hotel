<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ownership extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'user_id',
    ];

    /**
     * Get the property that owns the ownership.
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    /**
     * Get the user that owns the ownership.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
