<?php

namespace App\Workouts\Data\History;

use App\Routines\Models\Routine;
use App\Users\Models\User;
use App\Workouts\Models\Workout;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class HistoryIndexPageData extends Data
{
    /**
     * @param  DataCollection<int, HistoryWorkoutItemData>  $workouts
     * @param  Collection<int, array{slug: string, name: string}>  $routineFilterOptions
     */
    public function __construct(
        #[DataCollectionOf(HistoryWorkoutItemData::class)]
        public DataCollection $workouts,
        public Collection $routineFilterOptions,
        public ?string $routineSlug = null,
    ) {}

    public static function forUser(User $user, ?int $routineId = null, ?string $routineSlug = null): self
    {
        $query = Workout::query()
            ->with('routine')
            ->where('user_id', $user->id)
            ->finished()
            ->orderByDesc('finished_at');

        if ($routineId !== null) {
            $query->where('routine_id', $routineId);
        }

        $workouts = $query->get()->map(fn (Workout $workout): HistoryWorkoutItemData => HistoryWorkoutItemData::fromWorkout($workout));

        $routineFilterOptions = Routine::query()
            ->where('user_id', $user->id)
            ->orderBy('name')
            ->get()
            ->map(fn (Routine $routine): array => [
                'slug' => $routine->getSlug(),
                'name' => $routine->getName(),
            ]);

        return new self(
            workouts: HistoryWorkoutItemData::collect($workouts, DataCollection::class),
            routineFilterOptions: $routineFilterOptions,
            routineSlug: $routineSlug,
        );
    }
}
