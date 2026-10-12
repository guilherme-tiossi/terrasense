<?php

namespace App\Infrastructure\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

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

    protected static function booted(): void
    {
        static::saving(function (self $plant): void {
            $minimum = (float) $plant->minimum_allowed_humidity;
            $maximum = (float) $plant->maximum_allowed_humidity;

            if (
                $minimum < 0
                || $minimum > 100
                || $maximum < 0
                || $maximum > 100
                || $minimum > $maximum
            ) {
                throw new InvalidArgumentException(
                    'Os limites de umidade da planta devem estar entre 0 e 100, com o mínimo menor ou igual ao máximo.',
                );
            }
        });
    }

    public function userPlants(): HasMany
    {
        return $this->hasMany(UserPlant::class);
    }
}
