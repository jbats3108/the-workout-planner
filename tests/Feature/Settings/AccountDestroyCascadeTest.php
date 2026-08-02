<?php

namespace Tests\Feature\Settings;

use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Users\Models\User;
use App\Users\Services\PlateProfileService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AccountDestroyCascadeTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function deleting_account_removes_custom_exercises_instead_of_sharing_them(): void
    {
        $user = User::factory()->create();
        $custom = Exercise::factory()->create([
            'user_id' => $user->id,
            'name' => 'My Custom Lift',
            'slug' => 'my-custom-lift',
        ]);
        $shared = Exercise::factory()->create([
            'user_id' => null,
            'name' => 'Shared Bench',
            'slug' => 'shared-bench',
        ]);

        $this->actingAs($user)
            ->delete('/settings/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
        $this->assertDatabaseMissing('exercises', ['id' => $custom->id]);
        $this->assertNull(Exercise::withTrashed()->find($custom->id));
        $this->assertSame(0, Exercise::query()->shared()->where('slug', 'my-custom-lift')->count());
        $this->assertNotNull($shared->fresh());
    }

    #[Test]
    public function deleting_account_removes_routines_workouts_and_plate_profile(): void
    {
        $user = User::factory()->create();
        $exercise = Exercise::factory()->create(['user_id' => $user->id]);

        $routine = Routine::factory()->create(['user_id' => $user->id]);
        $block = RoutineBlock::create([
            'routine_id' => $routine->id,
            'position' => 1,
        ]);
        RoutineBlockExercise::create([
            'routine_block_id' => $block->id,
            'exercise_id' => $exercise->id,
            'position' => 1,
            'working_weight_g' => 80000,
            'prescribed_reps' => 5,
        ]);

        app(PlateProfileService::class)->ensureProfile($user);
        $this->assertNotNull($user->fresh()->plateProfile);

        $this->actingAs($user)
            ->delete('/settings/profile', ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertNull($user->fresh());
        $this->assertNull(Routine::withTrashed()->find($routine->id));
        $this->assertDatabaseMissing('routine_blocks', ['id' => $block->id]);
        $this->assertDatabaseMissing('exercises', ['id' => $exercise->id]);
        $this->assertDatabaseMissing('plate_profiles', ['user_id' => $user->id]);
    }
}
