<?php

namespace Tests\Feature\Shared\Http;

use App\Routines\Models\Routine;
use App\Workouts\Models\Workout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class SoftFailTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function missing_workout_play_redirects_authenticated_user_with_flash(): void
    {
        $this->actingAs($this->user)
            ->get(route('workouts.play', ['workout' => 999_999]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Workout not found. Check the URL and try again.');
    }

    #[Test]
    public function missing_routine_redirects_with_resource_flash(): void
    {
        $this->actingAs($this->user)
            ->get(route('routines.show', ['routine' => 999_999]))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'Routine not found. Check the URL and try again.');
    }

    #[Test]
    public function forbidden_workout_play_redirects_with_flash(): void
    {
        $workout = Workout::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->secondUser)
            ->get(route('workouts.play', $workout))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'You do not have access to that workout.');
    }

    #[Test]
    public function forbidden_routine_redirects_with_resource_flash(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $this->actingAs($this->secondUser)
            ->get(route('routines.show', $routine))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error', 'You do not have access to that routine.');
    }

    #[Test]
    public function guest_missing_route_still_returns_not_found(): void
    {
        $this->get('/register')->assertNotFound();
    }

    #[Test]
    public function admin_forbidden_stays_hard(): void
    {
        $this->actingAs($this->user)
            ->get(route('admin.index'))
            ->assertForbidden();
    }

    #[Test]
    public function mutation_forbidden_stays_hard(): void
    {
        $workout = Workout::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->actingAs($this->secondUser)
            ->post(route('workouts.finish', $workout))
            ->assertForbidden();
    }
}
