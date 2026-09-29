<?php

namespace App\Workouts\Data\History;

use App\Routines\Models\RoutineBlockExercise;
use App\Shared\Support\Weight;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class HistoricalCreateExerciseData extends Data
{
    public function __construct(
        public readonly int $position,
        public readonly string $name,
        public readonly ?string $equipment,
        public readonly float $workingWeightKg,
        public readonly ?string $note = null,
        public readonly ?int $prescribedReps = null,
        public readonly ?string $deloadName = null,
        public readonly ?string $deloadEquipment = null,
        public readonly ?float $deloadWorkingWeightKg = null,
        public readonly ?string $deloadNote = null,
        public readonly string $prescriptionMode = 'reps',
        public readonly ?int $prescribedDurationSeconds = null,
        public readonly float $deloadWeightFactor = 0.5,
        public readonly float $deloadRepsFactor = 0.5,
    ) {}

    public static function fromRoutineBlockExercise(RoutineBlockExercise $exercise): self
    {
        $hasAlternate = $exercise->hasDeloadAlternate() && $exercise->deloadExercise !== null;

        return new self(
            position: $exercise->position,
            name: $exercise->exercise->getName(),
            equipment: $exercise->exercise->equipment?->value,
            workingWeightKg: Weight::gramsToKg($exercise->working_weight_g),
            note: $exercise->note,
            prescribedReps: $exercise->prescribed_reps,
            deloadName: $hasAlternate ? $exercise->deloadExercise->getName() : null,
            deloadEquipment: $hasAlternate ? $exercise->deloadExercise->equipment?->value : null,
            deloadWorkingWeightKg: $hasAlternate
                ? Weight::gramsToKg((int) $exercise->deload_working_weight_g)
                : null,
            deloadNote: $hasAlternate ? $exercise->deload_note : null,
            prescriptionMode: $exercise->prescription_mode?->value ?? (string) ($exercise->prescription_mode ?? 'reps'),
            prescribedDurationSeconds: $exercise->prescribed_duration_seconds,
            deloadWeightFactor: (float) $exercise->deload_weight_factor,
            deloadRepsFactor: (float) $exercise->deload_reps_factor,
        );
    }
}
