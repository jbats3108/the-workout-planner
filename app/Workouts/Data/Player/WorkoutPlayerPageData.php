<?php

namespace App\Workouts\Data\Player;

use App\Users\Models\User;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutBlock;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class WorkoutPlayerPageData extends Data
{
    /**
     * @param  DataCollection<int, WorkoutPlayerBlockData>  $blocks
     */
    public function __construct(
        public readonly string $id,
        public readonly string $routineName,
        public readonly string $mode,
        public readonly string $status,
        public readonly string $weightUnit,
        #[DataCollectionOf(WorkoutPlayerBlockData::class)]
        public readonly DataCollection $blocks,
    ) {}

    public static function fromWorkout(Workout $workout, User $user): self
    {
        $workout->loadMissing([
            'routine',
            'blocks.blockExercises',
            'blocks.setGroups.sets.segments',
            'blocks.setGroups.warmUpSteps',
        ]);

        return new self(
            id: $workout->ulid,
            routineName: $workout->routine->getName(),
            mode: $workout->mode->value,
            status: $workout->status->value,
            weightUnit: $user->weight_unit->value,
            blocks: WorkoutPlayerBlockData::collect(
                $workout->blocks->map(fn (WorkoutBlock $block) => WorkoutPlayerBlockData::fromBlock($block)),
                DataCollection::class,
            ),
        );
    }
}
