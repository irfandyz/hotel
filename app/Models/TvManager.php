<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TvManager extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'guest_name',
        'image',
        'birth_date',
        'room_number',
        'check_in_date',
        'check_out_date',
        'status',
    ];

    protected $casts = [
        'birth_date' => 'date',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
