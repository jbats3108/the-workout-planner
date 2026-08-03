<?php

namespace App\Routines\Data;

use App\Routines\Models\Routine;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
final class RoutineData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly ?float $deloadWeightFactor = null,
        public readonly ?float $deloadRepsFactor = null,
        public readonly bool $canStart = false,
        public readonly int $normalsSinceDeload = 0,
        public readonly bool $hasFinishedDeload = false,
        public readonly int $deloadEveryN = 3,
    ) {}

    public static function fromRoutine(
        Routine $routine,
        int $normalsSinceDeload = 0,
        bool $hasFinishedDeload = false,
    ): RoutineData {
        $routine->loadMissing('blocks.blockExercises');

        $hasExercises = $routine->blocks->contains(
            fn ($block) => $block->blockExercises->isNotEmpty()
        );

        return new self(
            $routine->id,
            $routine->getSlug(),
            $routine->getName(),
            $routine->deload_weight_factor !== null ? (float) $routine->deload_weight_factor : null,
            $routine->deload_reps_factor !== null ? (float) $routine->deload_reps_factor : null,
            canStart: $hasExercises,
            normalsSinceDeload: $normalsSinceDeload,
            hasFinishedDeload: $hasFinishedDeload,
            deloadEveryN: (int) ($routine->deload_every_n ?? 3),
        );
    }
}
