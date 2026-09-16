<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPlant extends Model
{
    protected $fillable = [
        'user_id',
        'plant_id',
        'location_key',
        'current_humidity',
    ];

    protected function casts(): array
    {
        return [
            'current_humidity' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plant(): BelongsTo
    {
        return $this->belongsTo(Plant::class);
    }
}
