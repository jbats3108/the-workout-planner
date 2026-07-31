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

        $this->actingAs($this->adminUser)->put(route('routines.update', $routine), [
            'name' => 'New Name',
            'blocks' => [],
        ])->assertForbidden();
    }

    #[Test]
    public function users_can_only_update_their_own_routines(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();

        $this->actingAs($this->secondUser)->put(route('routines.update', $routine), [
            'name' => 'New Name',
            'blocks' => [],
        ])->assertNotFound();
    }

    #[Test]
    public function owner_update_redirects_with_success(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();
        $exercise = Exercise::factory()->create();

        $this->actingAs($this->user)->put(route('routines.update', $routine), [
            'name' => 'New Name',
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 2,
            'blocks' => [
                [
                    'is_superset' => false,
                    'has_setup_after' => false,
                    'exercises' => [
                        [
                            'exercise_id' => $exercise->id,
                            'working_weight_kg' => 80,
                            'prescribed_reps' => 6,
                        ],
                    ],
                    'working' => ['set_count' => 3, 'rest_seconds' => 180],
                    'warm_up' => ['set_count' => 0, 'rest_seconds' => 60, 'steps' => []],
                ],
            ],
        ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('success', 'Routine saved.');
    }

    #[Test]
    public function owner_can_save_progression_overrides(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();
        $exercise = Exercise::factory()->create();

        $this->actingAs($this->user)->put(route('routines.update', $routine), [
            'name' => 'Progression Overrides',
            'deload_weight_factor' => 0.9,
            'deload_reps_factor' => 1,
            'blocks' => [
                [
                    'is_superset' => false,
                    'has_setup_after' => false,
                    'exercises' => [
                        [
                            'exercise_id' => $exercise->id,
                            'working_weight_kg' => 80,
                            'prescribed_reps' => 5,
                            'achievement_floor' => 3,
                            'progression_target' => null,
                        ],
                    ],
                    'working' => ['set_count' => 3, 'rest_seconds' => 180],
                    'warm_up' => ['set_count' => 0, 'rest_seconds' => 60, 'steps' => []],
                ],
            ],
        ])->assertRedirect(route('dashboard'));

        $row = $routine->fresh()->blocks()->first()->blockExercises()->first();
        $this->assertSame(3, $row->achievement_floor_override);
        $this->assertNull($row->progression_target_override);
    }

    #[Test]
    public function editor_validation_errors_surface_on_blocks(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();
        $exerciseA = Exercise::factory()->create();
        $exerciseB = Exercise::factory()->create();

        $this->actingAs($this->user)->put(route('routines.update', $routine), [
            'name' => 'Bad Superset Dropset',
            'blocks' => [
                [
                    'is_superset' => true,
                    'has_setup_after' => false,
                    'exercises' => [
                        [
                            'exercise_id' => $exerciseA->id,
                            'working_weight_kg' => 60,
                            'prescribed_reps' => 6,
                        ],
                        [
                            'exercise_id' => $exerciseB->id,
                            'working_weight_kg' => 40,
                            'prescribed_reps' => 8,
                        ],
                    ],
                    'working' => [
                        'set_count' => 2,
                        'rest_seconds' => 120,
                        'dropsets' => [
                            [
                                'set_index' => 0,
                                'segments' => [
                                    ['weight_kg' => 60],
                                    ['weight_kg' => 40],
                                ],
                            ],
                        ],
                    ],
                    'warm_up' => ['set_count' => 0, 'rest_seconds' => 60, 'steps' => []],
                ],
            ],
        ])
            ->assertRedirect()
            ->assertSessionHasErrors('blocks');
    }

    #[Test]
    public function edit_page_renders_dropset_props_for_owner(): void
    {
        $routine = Routine::factory()->withUser($this->user)->create();
        $exercise = Exercise::factory()->create();

        $this->actingAs($this->user)->put(route('routines.update', $routine), [
            'name' => 'Dropset Finisher',
            'blocks' => [
                [
                    'is_superset' => false,
                    'has_setup_after' => false,
                    'exercises' => [
                        [
                            'exercise_id' => $exercise->id,
                            'working_weight_kg' => 20,
                            'prescribed_reps' => 12,
                        ],
                    ],
                    'working' => [
                        'set_count' => 2,
                        'rest_seconds' => 90,
                        'dropsets' => [
                            [
                                'set_index' => 1,
                                'segments' => [
                                    ['weight_kg' => 20],
                                    ['weight_kg' => 8],
                                ],
                            ],
                        ],
                    ],
                    'warm_up' => ['set_count' => 0, 'rest_seconds' => 60, 'steps' => []],
                ],
            ],
        ])->assertRedirect(route('dashboard'));

        $this->actingAs($this->user)
            ->get(route('routines.edit', $routine))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('routines/Edit')
                ->has('warm_up_defaults')
                ->has('warm_up_defaults_scope')
                ->has('achievement_floor_default')
                ->missing('progression_target_default')
                ->where('routine.blocks.0.working.dropsets.0.set_index', 1)
                ->where('routine.blocks.0.working.dropsets.0.segments.0.weight_kg', 20)
                ->where('routine.blocks.0.working.dropsets.0.segments.1.weight_kg', 8)
                ->loadDeferredProps(fn ($page) => $page->has('exercises')));
    }
}
