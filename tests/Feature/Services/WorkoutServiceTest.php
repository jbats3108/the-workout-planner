<?php

namespace Tests\Feature\Services;

use App\Enums\SetGroupType;
use App\Exceptions\WorkoutServiceException;
use App\Models\Exercise;
use App\Models\Routine;
use App\Models\RoutineBlock;
use App\Models\RoutineBlockExercise;
use App\Models\RoutineSetGroup;
use App\Models\Workouts\WorkoutSet;
use App\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WorkoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private WorkoutService $workoutService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->workoutService = new WorkoutService;
    }

    #[Test]
    public function it_throw_an_exception_if_it_tries_to_create_a_workout_from_a_routine_with_no_exercises(): void
    {
        // Given
        $routine = Routine::factory()->create();

        // When
        try {
            $this->workoutService->createWorkout($routine);
        } catch (WorkoutServiceException $workoutServiceException) {
            // Then
            $this->assertSame('Unable to create a workout for a routine with no exercises',
                $workoutServiceException->getMessage());

            return;
        }

        $this->fail();
    }

    #[Test]
    public function it_creates_a_workout_from_a_routine(): void
    {
        // Given
        $routine = Routine::factory()->create();
        $this->seedRoutineBlockWithExercise($routine);

        // When
        $workout = $this->workoutService->createWorkout($routine);

        // Then
        $this->assertTrue($workout->routine->is($routine));
        $this->assertSame('in_progress', $workout->status->value);
        $this->assertSame('normal', $workout->mode->value);
    }

    #[Test]
    public function it_creates_workout_blocks_for_each_routine_block(): void
    {
        // Given
        $routine = Routine::factory()->create();
        $this->seedRoutineBlockWithExercise($routine);

        // When
        $workout = $this->workoutService->createWorkout($routine);

        // Then
        $this->assertCount(1, $workout->blocks);
    }

    #[Test]
    public function it_creates_the_right_number_of_sets_for_a_block_exercise(): void
    {
        // Given
        $routine = Routine::factory()->create();
        $this->seedRoutineBlockWithExercise($routine, setCount: 4);

        // When
        $workout = $this->workoutService->createWorkout($routine);

        // Then
        $workoutBlockExercise = $workout->blocks->first()->blockExercises->first();
        $sets = WorkoutSet::where('workout_block_exercise_id', $workoutBlockExercise->id)->get();

        $this->assertCount(4, $sets);
        $sets->each(fn (WorkoutSet $workoutSet, int $key) => $this->assertSame($key, $workoutSet->set_index));
    }

    private function seedRoutineBlockWithExercise(Routine $routine, int $setCount = 3): void
    {
        $block = RoutineBlock::create([
            'routine_id' => $routine->id,
            'position' => 1,
        ]);

        RoutineBlockExercise::create([
            'routine_block_id' => $block->id,
            'exercise_id' => Exercise::factory()->create()->id,
            'position' => 1,
            'working_weight_g' => 80000,
            'prescribed_reps' => 6,
        ]);

        RoutineSetGroup::create([
            'routine_block_id' => $block->id,
            'type' => SetGroupType::Working,
            'set_count' => $setCount,
        ]);
    }
}
