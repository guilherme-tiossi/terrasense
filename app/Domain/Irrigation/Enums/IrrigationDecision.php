<?php

namespace App\Domain\Irrigation\Enums;

enum IrrigationDecision: string
{
    case NoAction = 'NO_ACTION';
    case Wait = 'WAIT';
    case Irrigate = 'IRRIGATE';
}
