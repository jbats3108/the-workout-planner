<?php

namespace Tests\Feature\Workouts\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineSetGroup;
use App\Shared\Enums\SetGroupType;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class DiscardWorkoutControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function it_discards_an_in_progress_workout(): void
    {
        $workout = $this->createWorkoutForUser();

        $this->actingAs($this->user)
            ->post(route('workouts.discard', $workout))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success');

        $this->assertSame(WorkoutStatus::Discarded, $workout->fresh()->status);
    }

    #[Test]
    public function it_forbids_other_users(): void
    {
        $workout = $this->createWorkoutForUser();

        $this->actingAs($this->secondUser)
            ->post(route('workouts.discard', $workout))
            ->assertForbidden();
    }

    private function createWorkoutForUser()
    {
        $routine = Routine::factory()->withUser($this->user)->create();
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
            'set_count' => 1,
            'rest_seconds' => 90,
        ]);

        return app(WorkoutService::class)->createWorkout($routine);
    }
}
