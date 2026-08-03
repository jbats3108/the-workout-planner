<?php

namespace Tests\Unit\Workouts\Services;

use App\Routines\Models\Routine;
use App\Users\Models\User;
use App\Workouts\Enums\WorkoutMode;
use App\Workouts\Enums\WorkoutStatus;
use App\Workouts\Models\Workout;
use App\Workouts\Services\StandardsSinceDeloadCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StandardsSinceDeloadCounterTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_counts_all_finished_standards_when_never_deloaded(): void
    {
        $user = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $user->id]);
        $other = Routine::factory()->create(['user_id' => $user->id]);

        $this->finishedWorkout($user, $routine, WorkoutMode::Standard, now()->subDays(3));
        $this->finishedWorkout($user, $routine, WorkoutMode::Standard, now()->subDays(2));
        $this->finishedWorkout($user, $other, WorkoutMode::Standard, now()->subDay());

        $summaries = (new StandardsSinceDeloadCounter)->summarizeByRoutineId($user, [$routine->id, $other->id]);

        $this->assertSame(2, $summaries[$routine->id]['count']);
        $this->assertFalse($summaries[$routine->id]['has_finished_deload']);
        $this->assertSame(1, $summaries[$other->id]['count']);
        $this->assertFalse($summaries[$other->id]['has_finished_deload']);
    }

    #[Test]
    public function it_resets_per_routine_after_that_routines_deload(): void
    {
        $user = User::factory()->create();
        $a = Routine::factory()->create(['user_id' => $user->id]);
        $b = Routine::factory()->create(['user_id' => $user->id]);

        $this->finishedWorkout($user, $a, WorkoutMode::Standard, now()->subDays(5));
        $this->finishedWorkout($user, $a, WorkoutMode::Deload, now()->subDays(4));
        $this->finishedWorkout($user, $a, WorkoutMode::Standard, now()->subDays(3));
        $this->finishedWorkout($user, $a, WorkoutMode::Standard, now()->subDays(2));

        $this->finishedWorkout($user, $b, WorkoutMode::Standard, now()->subDays(5));
        $this->finishedWorkout($user, $b, WorkoutMode::Standard, now()->subDays(1));

        $summaries = (new StandardsSinceDeloadCounter)->summarizeByRoutineId($user, [$a->id, $b->id]);

        $this->assertSame(2, $summaries[$a->id]['count']);
        $this->assertTrue($summaries[$a->id]['has_finished_deload']);
        $this->assertSame(2, $summaries[$b->id]['count']);
        $this->assertFalse($summaries[$b->id]['has_finished_deload']);
    }

    #[Test]
    public function it_ignores_in_progress_discarded_and_other_users(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $user->id]);
        $otherRoutine = Routine::factory()->create(['user_id' => $otherUser->id]);

        $this->finishedWorkout($user, $routine, WorkoutMode::Standard, now()->subDay());

        Workout::factory()->create([
            'user_id' => $user->id,
            'routine_id' => $routine->id,
            'mode' => WorkoutMode::Standard,
            'status' => WorkoutStatus::InProgress,
            'started_at' => now(),
            'finished_at' => null,
        ]);

        Workout::factory()->create([
            'user_id' => $user->id,
            'routine_id' => $routine->id,
            'mode' => WorkoutMode::Standard,
            'status' => WorkoutStatus::Discarded,
            'started_at' => now()->subHours(2),
            'finished_at' => now()->subHour(),
        ]);

        $this->finishedWorkout($otherUser, $otherRoutine, WorkoutMode::Standard, now());

        $summaries = (new StandardsSinceDeloadCounter)->summarizeByRoutineId($user, [$routine->id]);

        $this->assertSame(1, $summaries[$routine->id]['count']);
        $this->assertFalse($summaries[$routine->id]['has_finished_deload']);
    }

    private function finishedWorkout(User $user, Routine $routine, WorkoutMode $mode, Carbon $finishedAt): Workout
    {
        return Workout::factory()->create([
            'user_id' => $user->id,
            'routine_id' => $routine->id,
            'mode' => $mode,
            'status' => WorkoutStatus::Finished,
            'started_at' => $finishedAt->copy()->subHour(),
            'finished_at' => $finishedAt,
        ]);
    }
}
