<?php

namespace App\Application\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserPlantUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int $userPlantId
    ) {
    }
}
