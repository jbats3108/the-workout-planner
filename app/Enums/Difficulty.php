<?php

namespace App\Enums;

use App\Enums\Traits\HasValues;

enum Difficulty: string
{
    use HasValues;

    case BEGINNER     = 'beginner';
    case INTERMEDIATE = 'intermediate';
    case ADVANCED     = 'advanced';
}
