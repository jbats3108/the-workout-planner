<?php

namespace Tests\Feature\Routines\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\Helpers\UserHelper;
use Tests\TestCase;

class UpdateRoutineControllerTest extends TestCase
{
    use RefreshDatabase;
    use UserHelper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedUsers();
    }

    #[Test]
    public function admins_cannot_update_user_routines(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $response = $this->actingAs($this->adminUser)->put(route('routines.update', $routine), [
            'name' => 'New Name',
            'blocks' => [],
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function users_can_only_update_their_own_routines(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $response = $this->actingAs($this->secondUser)->put(route('routines.update', $routine), [
            'name' => 'New Name',
            'blocks' => [],
        ]);

        $response->assertForbidden();
    }

    #[Test]
    public function it_updates_the_routine_details_and_blocks(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();
        $exercise = Exercise::factory()->create();

        $response = $this->actingAs($this->user)->put(route('routines.update', $routine), [
            'name' => 'New Name',
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 2,
            'blocks' => [
                [
                    'is_superset' => false,
                    'has_setup_after' => true,
                    'exercises' => [
                        [
                            'exercise_id' => $exercise->id,
                            'working_weight_kg' => 80,
                            'prescribed_reps' => 6,
                            'achievement_floor' => 4,
                            'progression_target' => 6,
                        ],
                    ],
                    'working' => ['set_count' => 3, 'rest_seconds' => 180],
                    'warm_up' => ['set_count' => 2, 'rest_seconds' => 60, 'percents' => [50, 75]],
                ],
            ],
        ]);

        $response->assertRedirect(route('routines.edit', $routine));

        $routine->refresh();
        $this->assertSame('New Name', $routine->name);
        $this->assertCount(1, $routine->blocks);
        $block = $routine->blocks->first();
        $this->assertTrue($block->has_setup_after);
        $this->assertSame(80000, $block->blockExercises->first()->working_weight_g);
        $this->assertCount(2, $block->warmUpSetGroup->warmUpSteps);
    }

    #[Test]
    public function it_allows_saving_blocks_with_no_warm_up_percents(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();
        $exercise = Exercise::factory()->create();

        $response = $this->actingAs($this->user)->put(route('routines.update', $routine), [
            'name' => 'No Warmups',
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 2,
            'blocks' => [
                [
                    'is_superset' => false,
                    'has_setup_after' => false,
                    'exercises' => [
                        [
                            'exercise_id' => $exercise->id,
                            'working_weight_kg' => 60,
                            'prescribed_reps' => 6,
                            'achievement_floor' => null,
                            'progression_target' => null,
                        ],
                    ],
                    'working' => ['set_count' => 3, 'rest_seconds' => 120],
                    'warm_up' => ['set_count' => 0, 'rest_seconds' => 60, 'percents' => []],
                ],
            ],
        ]);

        $response->assertRedirect(route('routines.edit', $routine));
        $this->assertCount(1, $routine->fresh()->blocks);
    }

    #[Test]
    public function edit_page_renders_for_owner(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $response = $this->actingAs($this->user)->get(route('routines.edit', $routine));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('routines/Edit')
            ->has('routine')
            ->has('exercises'));
    }
}
