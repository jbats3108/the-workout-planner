<?php

namespace App\Workouts\Data\Progression;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class ApplyBumpsData extends Data
{
    /**
     * @param  list<int>  $routineBlockExerciseIds
     * @param  list<int>  $undoBumpRecordIds
     */
    public function __construct(
        public array $routineBlockExerciseIds,
        public array $undoBumpRecordIds = [],
    ) {}

    /** @return array<string, mixed> */
    public static function rules(): array
    {
        return [
            'routine_block_exercise_ids' => ['present', 'array'],
            'routine_block_exercise_ids.*' => ['integer', 'exists:routine_block_exercises,id'],
            'undo_bump_record_ids' => ['present', 'array'],
            'undo_bump_record_ids.*' => ['integer', 'exists:bump_records,id'],
        ];
    }
}
