<?php

namespace App\Exercises\Enums;

enum ExerciseEquipment: string
{
    case Barbell = 'barbell';
    case Dumbbell = 'dumbbell';
    case Kettlebells = 'kettlebells';
    case Cable = 'cable';
    case Machine = 'machine';
    case Bands = 'bands';
    case MedicineBall = 'medicine_ball';
    case BodyOnly = 'body_only';
    case FoamRoll = 'foam_roll';
    case ExerciseBall = 'exercise_ball';
    case EzCurlBar = 'ez_curl_bar';
    case Other = 'other';

    public function usesBarbellPlates(): bool
    {
        return match ($this) {
            self::Barbell, self::EzCurlBar => true,
            default => false,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Barbell => 'Barbell',
            self::Dumbbell => 'Dumbbell',
            self::Kettlebells => 'Kettlebells',
            self::Cable => 'Cable',
            self::Machine => 'Machine',
            self::Bands => 'Bands',
            self::MedicineBall => 'Medicine ball',
            self::BodyOnly => 'Body only',
            self::FoamRoll => 'Foam roll',
            self::ExerciseBall => 'Exercise ball',
            self::EzCurlBar => 'E-Z curl bar',
            self::Other => 'Other',
        };
    }
}
