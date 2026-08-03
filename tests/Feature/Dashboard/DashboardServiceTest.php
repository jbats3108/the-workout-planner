<?php

namespace Tests\Feature\Dashboard;

use App\Dashboard\Services\DashboardService;
use App\Exercises\Models\Exercise;
use App\Routines\Data\RoutineData;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineSetGroup;
use App\Shared\Enums\SetGroupType;
use App\Workouts\Services\WorkoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();

        // Drop seeded routines
        Routine::truncate();
    }

    #[Test]
    public function it_returns_the_users_routines(): void
    {
        // Given
        $routines = Routine::factory(5)->withUser($this->user)->create();

        $dashboardService = new DashboardService;

        // When
        $dashboardData = $dashboardService->getDashboardData($this->user);

        // Then
        $dashboardRoutines = $dashboardData->routines;

        $this->assertCount(5, $dashboardRoutines);

        $routineData = $routines
            ->sortBy('id')
            ->values()
            ->map(fn (Routine $routine): RoutineData => RoutineData::fromRoutine($routine));

        $this->assertEquals(
            $routineData->all(),
            $dashboardRoutines->sortBy('id')->values()->all(),
        );

    }

    #[Test]
    public function it_includes_recent_finished_workouts(): void
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
        ]);

        $workout = app(WorkoutService::class)->createWorkout($routine);
        $set = $workout->blocks->first()->setGroups->first()->sets->first();
        app(WorkoutService::class)->completeSet($set, reps: 6, weightGrams: 80000);
        app(WorkoutService::class)->finishWorkout($workout);

        $dashboardData = (new DashboardService)->getDashboardData($this->user);

        $this->assertCount(1, $dashboardData->recentFinishedWorkouts);
        $this->assertSame($workout->ulid, $dashboardData->recentFinishedWorkouts->first()->id);
    }
}
