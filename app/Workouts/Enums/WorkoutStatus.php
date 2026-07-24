<?php

namespace App\Workouts\Enums;

enum WorkoutStatus: string
{
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Discarded = 'discarded';
}
