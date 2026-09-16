<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plant extends Model
{
    protected $fillable = [
        'name',
        'minimum_allowed_humidity',
        'maximum_allowed_humidity',
    ];

    protected function casts(): array
    {
        return [
            'minimum_allowed_humidity' => 'decimal:2',
            'maximum_allowed_humidity' => 'decimal:2',
        ];
    }

    public function userPlants(): HasMany
    {
        return $this->hasMany(UserPlant::class);
    }
}
