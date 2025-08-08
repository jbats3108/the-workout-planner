<?php

namespace App\Enums;

use App\Enums\Traits\HasValues;

enum MovementType: string
{
    use HasValues;

    case PUSH = 'push';
    case PULL = 'pull';
    case LOWER = 'lower';

}
