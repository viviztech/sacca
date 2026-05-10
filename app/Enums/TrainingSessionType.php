<?php

namespace App\Enums;

enum TrainingSessionType: string
{
    case Theory = 'theory';
    case Practical = 'practical';
    case Simulation = 'simulation';

    public function label(): string
    {
        return match ($this) {
            self::Theory => 'Theory',
            self::Practical => 'Practical',
            self::Simulation => 'Simulation',
        };
    }
}
