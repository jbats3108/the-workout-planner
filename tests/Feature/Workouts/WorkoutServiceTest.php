<?php

namespace Tests\Feature\Workouts;

use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineSetGroup;
use App\Shared\Enums\SetGroupType;
use App\Workouts\Exceptions\WorkoutServiceException;
use App\Workouts\Models\WorkoutSet;
use App\Workouts\Services\WorkoutService;
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
        $this->workoutService = app(WorkoutService::class);
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

    #[Test]
    public function it_applies_deload_factors_when_starting_in_deload_mode(): void
    {
        $routine = Routine::factory()->create([
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 0.5,
        ]);
        $this->seedRoutineBlockWithExercise($routine, setCount: 1);

        $workout = $this->workoutService->createWorkout($routine, \App\Workouts\Enums\WorkoutMode::Deload);

        $exercise = $workout->blocks->first()->blockExercises->first();

        $this->assertSame('deload', $workout->mode->value);
        $this->assertSame(40000, $exercise->working_weight_g);
        $this->assertSame(3, $exercise->prescribed_reps);
    }

    #[Test]
    public function it_rejects_a_second_in_progress_workout_for_the_same_user(): void
    {
        $routine = Routine::factory()->create();
        $this->seedRoutineBlockWithExercise($routine);
        $this->workoutService->createWorkout($routine);

        $other = Routine::factory()->create(['user_id' => $routine->user_id]);
        $this->seedRoutineBlockWithExercise($other);

        $this->expectException(WorkoutServiceException::class);
        $this->expectExceptionMessage(WorkoutService::ALREADY_IN_PROGRESS_ERROR);

        $this->workoutService->createWorkout($other);
    }

    #[Test]
    public function it_adds_a_working_set_round_to_the_block(): void
    {
        $routine = Routine::factory()->create();
        $this->seedRoutineBlockWithExercise($routine, setCount: 2);
        $workout = $this->workoutService->createWorkout($routine);
        $block = $workout->blocks->first();

        $this->workoutService->addWorkingSet($block);

        $working = $block->fresh()->workingSetGroup;
        $this->assertSame(3, $working->set_count);
        $this->assertCount(3, $working->sets);
    }

    #[Test]
    public function it_removes_an_incomplete_working_set_round(): void
    {
        $routine = Routine::factory()->create();
        $this->seedRoutineBlockWithExercise($routine, setCount: 2);
        $workout = $this->workoutService->createWorkout($routine);
        $set = WorkoutSet::query()
            ->whereHas('setGroup.block', fn ($q) => $q->where('workout_id', $workout->id))
            ->where('set_index', 1)
            ->firstOrFail();

        $this->workoutService->removeWorkingSetRound($set);

        $working = $workout->blocks->first()->fresh()->workingSetGroup;
        $this->assertSame(1, $working->set_count);
        $this->assertCount(1, $working->sets);
    }

    #[Test]
    public function it_does_not_remove_the_last_working_set(): void
    {
        $routine = Routine::factory()->create();
        $this->seedRoutineBlockWithExercise($routine, setCount: 1);
        $workout = $this->workoutService->createWorkout($routine);
        $set = WorkoutSet::query()
            ->whereHas('setGroup.block', fn ($q) => $q->where('workout_id', $workout->id))
            ->firstOrFail();

        $this->expectException(WorkoutServiceException::class);
        $this->expectExceptionMessage(WorkoutService::CANNOT_REMOVE_LAST_WORKING_SET_ERROR);

        $this->workoutService->removeWorkingSetRound($set);
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
