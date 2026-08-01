<?php

namespace Tests\Unit\Routines\Services;

use App\Exercises\Models\Exercise;
use App\Routines\Data\Editor\SyncRoutineBlockData;
use App\Routines\Data\Editor\SyncRoutineData;
use App\Routines\Data\Editor\SyncWarmUpData;
use App\Routines\Data\Editor\SyncWarmUpStepData;
use App\Routines\Models\Routine;
use App\Routines\Services\RoutineEditorService;
use App\Users\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Spatie\LaravelData\DataCollection;
use Tests\TestCase;

class RoutineEditorServiceTest extends TestCase
{
    use RefreshDatabase;

    private RoutineEditorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(RoutineEditorService::class);
    }

    #[Test]
    public function sync_persists_name_blocks_weight_and_warm_ups(): void
    {
        $routine = Routine::factory()->create(['name' => 'Old']);
        $exercise = Exercise::factory()->create();

        $result = $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'New Name',
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 2,
            'blocks' => [
                $this->singleBlockPayload($exercise->id, [
                    'working_weight_kg' => 80,
                    'prescribed_reps' => 6,
                    'warm_up' => [
                        'set_count' => 2,
                        'rest_seconds' => 60,
                        'steps' => [
                            ['percent' => 50, 'reps' => 5],
                            ['percent' => 75, 'reps' => 3],
                        ],
                    ],
                    'has_setup_after' => true,
                    'has_setup_after_warm_up' => true,
                ]),
            ],
        ]));

        $this->assertSame('New Name', $result->name);
        $this->assertCount(1, $result->blocks);
        $block = $result->blocks->first();
        // Setup-after-block is disabled on the final routine block to avoid an end-of-workout pause.
        $this->assertFalse($block->has_setup_after);
        $this->assertTrue($block->has_setup_after_warm_up);
        $this->assertSame(80000, $block->blockExercises->first()->working_weight_g);
        $steps = $block->warmUpSetGroup->warmUpSteps;
        $this->assertCount(2, $steps);
        $this->assertSame(50, $steps[0]->percent_of_working);
        $this->assertSame(5, $steps[0]->reps);
        $this->assertSame(75, $steps[1]->percent_of_working);
        $this->assertSame(3, $steps[1]->reps);
    }

    #[Test]
    public function sync_persists_setup_after_on_warm_up_steps(): void
    {
        $routine = Routine::factory()->create();
        $exercise = Exercise::factory()->create();

        $result = $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'Warm-up setup',
            'deload_weight_factor' => 0.8,
            'deload_reps_factor' => 0.8,
            'blocks' => [
                $this->singleBlockPayload($exercise->id, [
                    'warm_up' => [
                        'set_count' => 2,
                        'rest_seconds' => 60,
                        'steps' => [
                            ['percent' => 40, 'reps' => 5, 'has_setup_after' => true],
                            ['percent' => 60, 'reps' => 3, 'has_setup_after' => false],
                        ],
                    ],
                ]),
            ],
        ]));

        $steps = $result->blocks->first()->warmUpSetGroup->warmUpSteps;
        $this->assertTrue($steps[0]->has_setup_after);
        $this->assertFalse($steps[1]->has_setup_after);
    }

    #[Test]
    public function sync_ignores_setup_after_on_the_final_block(): void
    {
        $routine = Routine::factory()->create();
        $exercise = Exercise::factory()->create();

        $result = $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'Final block setup',
            'deload_weight_factor' => 0.8,
            'deload_reps_factor' => 0.8,
            'blocks' => [
                $this->singleBlockPayload($exercise->id, ['has_setup_after' => false]),
                $this->singleBlockPayload($exercise->id, ['has_setup_after' => true]),
            ],
        ]));

        $blocks = $result->blocks->sortBy('position')->values();
        $this->assertFalse($blocks[0]->has_setup_after);
        $this->assertFalse($blocks[1]->has_setup_after);
    }

    #[Test]
    public function sync_allows_blocks_with_no_warm_up_steps(): void
    {
        $routine = Routine::factory()->create();
        $exercise = Exercise::factory()->create();

        $result = $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'No Warmups',
            'blocks' => [
                $this->singleBlockPayload($exercise->id, [
                    'warm_up' => ['set_count' => 0, 'rest_seconds' => 60, 'steps' => []],
                ]),
            ],
        ]));

        $this->assertCount(1, $result->blocks);
        $this->assertCount(0, $result->blocks->first()->warmUpSetGroup->warmUpSteps);
    }

    #[Test]
    public function sync_persists_dropset_recipes(): void
    {
        $routine = Routine::factory()->create();
        $exercise = Exercise::factory()->create();

        $result = $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'Dropset Finisher',
            'blocks' => [
                $this->singleBlockPayload($exercise->id, [
                    'working_weight_kg' => 20,
                    'prescribed_reps' => 12,
                    'working' => [
                        'set_count' => 2,
                        'rest_seconds' => 90,
                        'dropsets' => [
                            [
                                'set_index' => 1,
                                'segments' => [
                                    ['weight_kg' => 20],
                                    ['weight_kg' => 16],
                                    ['weight_kg' => 12],
                                    ['weight_kg' => 8],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
        ]));

        $segments = $result->blocks->first()->workingSetGroup->dropsetSegments
            ->where('set_index', 1)
            ->sortBy('position')
            ->values();

        $this->assertCount(4, $segments);
        $this->assertSame([20000, 16000, 12000, 8000], $segments->pluck('weight_g')->all());
    }

    #[Test]
    public function sync_rejects_dropsets_on_supersets(): void
    {
        $routine = Routine::factory()->create();
        $exerciseA = Exercise::factory()->create();
        $exerciseB = Exercise::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Dropsets are not supported on supersets.');

        $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'Bad Superset',
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
        ]));
    }

    #[Test]
    public function sync_rejects_single_block_with_wrong_exercise_count(): void
    {
        $routine = Routine::factory()->create();
        $exerciseA = Exercise::factory()->create();
        $exerciseB = Exercise::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A non-superset must have exactly one exercise.');

        $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'Bad Single',
            'blocks' => [
                [
                    'is_superset' => false,
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
                    'working' => ['set_count' => 3, 'rest_seconds' => 120],
                    'warm_up' => ['set_count' => 0, 'rest_seconds' => 60, 'steps' => []],
                ],
            ],
        ]));
    }

    #[Test]
    public function sync_rejects_superset_with_wrong_exercise_count(): void
    {
        $routine = Routine::factory()->create();
        $exercise = Exercise::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A superset must have exactly two exercises.');

        $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'Bad Superset Count',
            'blocks' => [
                [
                    'is_superset' => true,
                    'has_setup_after' => false,
                    'exercises' => [
                        [
                            'exercise_id' => $exercise->id,
                            'working_weight_kg' => 60,
                            'prescribed_reps' => 6,
                        ],
                    ],
                    'working' => ['set_count' => 3, 'rest_seconds' => 120],
                    'warm_up' => ['set_count' => 0, 'rest_seconds' => 60, 'steps' => []],
                ],
            ],
        ]));
    }

    #[Test]
    public function sync_rejects_dropset_with_fewer_than_two_segments(): void
    {
        $routine = Routine::factory()->create();
        $exercise = Exercise::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A dropset requires at least two segments.');

        $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'Short Dropset',
            'blocks' => [
                $this->singleBlockPayload($exercise->id, [
                    'working' => [
                        'set_count' => 2,
                        'rest_seconds' => 90,
                        'dropsets' => [
                            [
                                'set_index' => 0,
                                'segments' => [
                                    ['weight_kg' => 20],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
        ]));
    }

    #[Test]
    public function sync_rejects_dropset_index_outside_set_count(): void
    {
        $routine = Routine::factory()->create();
        $exercise = Exercise::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Dropset set index 2 is outside working set count 2.');

        $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'Out Of Range',
            'blocks' => [
                $this->singleBlockPayload($exercise->id, [
                    'working' => [
                        'set_count' => 2,
                        'rest_seconds' => 90,
                        'dropsets' => [
                            [
                                'set_index' => 2,
                                'segments' => [
                                    ['weight_kg' => 20],
                                    ['weight_kg' => 16],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
        ]));
    }

    #[Test]
    public function sync_rejects_duplicate_dropset_indexes(): void
    {
        $routine = Routine::factory()->create();
        $exercise = Exercise::factory()->create();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate dropset recipe for set index 0.');

        $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'Dup Dropset',
            'blocks' => [
                $this->singleBlockPayload($exercise->id, [
                    'working' => [
                        'set_count' => 2,
                        'rest_seconds' => 90,
                        'dropsets' => [
                            [
                                'set_index' => 0,
                                'segments' => [
                                    ['weight_kg' => 20],
                                    ['weight_kg' => 16],
                                ],
                            ],
                            [
                                'set_index' => 0,
                                'segments' => [
                                    ['weight_kg' => 18],
                                    ['weight_kg' => 14],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
        ]));
    }

    #[Test]
    public function sync_rejects_unavailable_exercise(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $routine = Routine::factory()->withUser($owner)->create();
        $foreignExercise = Exercise::factory()->create(['user_id' => $other->id]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Exercise {$foreignExercise->id} is not available for this routine.");

        $this->service->sync($routine, SyncRoutineData::from([
            'name' => 'Foreign Exercise',
            'blocks' => [
                $this->singleBlockPayload($foreignExercise->id),
            ],
        ]));
    }

    #[Test]
    public function sync_clamps_warm_up_percent_and_reps_to_valid_range(): void
    {
        $routine = Routine::factory()->create();
        $exercise = Exercise::factory()->create();

        // Build via from() then replace warm-up steps with an out-of-range direct instance
        // so Spatie Min/Max is bypassed and service clamp is exercised.
        $data = SyncRoutineData::from([
            'name' => 'Clamped',
            'blocks' => [
                $this->singleBlockPayload($exercise->id, [
                    'warm_up' => [
                        'set_count' => 1,
                        'rest_seconds' => 60,
                        'steps' => [
                            ['percent' => 50, 'reps' => 5],
                        ],
                    ],
                ]),
            ],
        ]);

        $block = $data->blocks->first();
        $overRange = new SyncWarmUpData(
            setCount: 1,
            restSeconds: 60,
            steps: new DataCollection(
                SyncWarmUpStepData::class,
                [new SyncWarmUpStepData(percent: 150, reps: 200)],
            ),
        );

        $clampedBlock = new SyncRoutineBlockData(
            isSuperset: $block->isSuperset,
            hasSetupAfter: $block->hasSetupAfter,
            exercises: $block->exercises,
            working: $block->working,
            warmUp: $overRange,
            hasSetupAfterWarmUp: $block->hasSetupAfterWarmUp,
        );

        $result = $this->service->sync($routine, new SyncRoutineData(
            name: 'Clamped',
            blocks: new DataCollection(
                SyncRoutineBlockData::class,
                [$clampedBlock],
            ),
        ));

        $step = $result->blocks->first()->warmUpSetGroup->warmUpSteps->first();
        $this->assertSame(100, $step->percent_of_working);
        $this->assertSame(100, $step->reps);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function singleBlockPayload(int $exerciseId, array $overrides = []): array
    {
        $exercise = [
            'exercise_id' => $exerciseId,
            'working_weight_kg' => $overrides['working_weight_kg'] ?? 60,
            'prescribed_reps' => $overrides['prescribed_reps'] ?? 6,
            'achievement_floor' => $overrides['achievement_floor'] ?? null,
            'progression_target' => $overrides['progression_target'] ?? null,
        ];

        return [
            'is_superset' => false,
            'has_setup_after' => $overrides['has_setup_after'] ?? false,
            'has_setup_after_warm_up' => $overrides['has_setup_after_warm_up'] ?? false,
            'exercises' => [$exercise],
            'working' => $overrides['working'] ?? ['set_count' => 3, 'rest_seconds' => 120],
            'warm_up' => $overrides['warm_up'] ?? ['set_count' => 0, 'rest_seconds' => 60, 'steps' => []],
        ];
    }
}
