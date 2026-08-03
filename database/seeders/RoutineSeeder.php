<?php

namespace Database\Seeders;

use App\Exercises\Enums\ExerciseEquipment;
use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineDropsetSegment;
use App\Routines\Models\RoutineSetGroup;
use App\Routines\Models\RoutineWarmUpStep;
use App\Shared\Enums\SetGroupType;
use App\Shared\Support\Weight;
use App\Users\Enums\WarmUpDefaultsScope;
use App\Users\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Demo routines for local Play / editor testing (user1@test.com).
 *
 * | Routine | Covers |
 * |---|---|
 * | Barbell Strength | Barbell + plates, WU first block only, setup after WU |
 * | Dumbbell Accessories | Dumbbell + bodyweight dip @ 28.75, WU on every block |
 * | Superset Pump | Supersets, WU on some blocks only, mixed equipment |
 * | Dropset Finishers | Dropsets mixed with single sets, barbell + DB |
 */
class RoutineSeeder extends Seeder
{
    /** @var list<string> */
    public const DEMO_NAMES = [
        'Barbell Strength',
        'Dumbbell Accessories',
        'Superset Pump',
        'Dropset Finishers',
    ];

    /** @var list<array{percent: int, reps: int}> */
    private const DEFAULT_WARM_UPS = [
        ['percent' => 40, 'reps' => 8],
        ['percent' => 60, 'reps' => 5],
        ['percent' => 80, 'reps' => 3],
    ];

    public function run(): void
    {
        $user = User::query()->where('email', 'user1@test.com')->first()
            ?? User::query()->whereHas('roles', fn ($q) => $q->where('name', 'user'))->first();

        if ($user === null) {
            return;
        }

        $user->forceFill([
            'warm_up_defaults_scope' => WarmUpDefaultsScope::FirstBlock,
            'warm_up_steps_default' => self::DEFAULT_WARM_UPS,
        ])->save();

        $this->replaceDemoRoutines($user);

        $this->seedBarbellStrength($user);
        $this->seedDumbbellAccessories($user);
        $this->seedSupersetPump($user);
        $this->seedDropsetFinishers($user);
    }

    private function replaceDemoRoutines(User $user): void
    {
        $routines = Routine::query()
            ->where('user_id', $user->id)
            ->whereIn('name', self::DEMO_NAMES)
            ->get();

        foreach ($routines as $routine) {
            if ($routine->workouts()->exists()) {
                $routine->update(['name' => $routine->name.' (archived)']);
                $routine->delete();

                continue;
            }

            $routine->forceDelete();
        }

        // Hollow leftovers from the old name-only seeder.
        Routine::query()
            ->where('user_id', $user->id)
            ->whereDoesntHave('blocks')
            ->whereDoesntHave('workouts')
            ->forceDelete();
    }

    private function seedBarbellStrength(User $user): void
    {
        $routine = $this->createRoutine($user, 'Barbell Strength');

        // Block 1 — only warm-up in this routine; setup before working; plate guide.
        $this->addBlock($routine, position: 1, exercises: [
            ['name' => 'Barbell Bench Press - Medium Grip', 'equipment' => ExerciseEquipment::Barbell, 'kg' => 80, 'reps' => 5],
        ], workingSets: 3, workingRest: 180, warmUps: self::DEFAULT_WARM_UPS, setupAfterWarmUp: true);

        $this->addBlock($routine, position: 2, exercises: [
            ['name' => 'Bent Over Barbell Row', 'equipment' => ExerciseEquipment::Barbell, 'kg' => 70, 'reps' => 6],
        ], workingSets: 3, workingRest: 150, warmUps: [], setupAfter: true);

        $this->addBlock($routine, position: 3, exercises: [
            ['name' => 'Barbell Squat', 'equipment' => ExerciseEquipment::Barbell, 'kg' => 100, 'reps' => 5],
        ], workingSets: 3, workingRest: 180, warmUps: []);
    }

    private function seedDumbbellAccessories(User $user): void
    {
        $routine = $this->createRoutine($user, 'Dumbbell Accessories');
        $wu = self::DEFAULT_WARM_UPS;

        $this->addBlock($routine, position: 1, exercises: [
            ['name' => 'Dumbbell Bench Press', 'equipment' => ExerciseEquipment::Dumbbell, 'kg' => 28, 'reps' => 8],
        ], workingSets: 3, workingRest: 90, warmUps: $wu);

        $this->addBlock($routine, position: 2, exercises: [
            ['name' => 'Arnold Dumbbell Press', 'equipment' => ExerciseEquipment::Dumbbell, 'kg' => 16, 'reps' => 10],
        ], workingSets: 3, workingRest: 90, warmUps: $wu);

        $this->addBlock($routine, position: 3, exercises: [
            ['name' => 'Alternate Hammer Curl', 'equipment' => ExerciseEquipment::Dumbbell, 'kg' => 14, 'reps' => 12],
        ], workingSets: 3, workingRest: 60, warmUps: $wu);

        // Fractional load (no plate guide) — gym-test 28.75 dip belt.
        $this->addBlock($routine, position: 4, exercises: [
            ['name' => 'Bench Dips', 'equipment' => ExerciseEquipment::BodyOnly, 'kg' => 28.75, 'reps' => 8],
        ], workingSets: 3, workingRest: 90, warmUps: $wu, setupAfter: true);
    }

    private function seedSupersetPump(User $user): void
    {
        $routine = $this->createRoutine($user, 'Superset Pump');

        // WU on first block only of the three — "some" blocks.
        $this->addBlock($routine, position: 1, exercises: [
            ['name' => 'Close-Grip Barbell Bench Press', 'equipment' => ExerciseEquipment::Barbell, 'kg' => 60, 'reps' => 8],
            ['name' => 'Barbell Curl', 'equipment' => ExerciseEquipment::Barbell, 'kg' => 30, 'reps' => 10],
        ], workingSets: 3, workingRest: 120, warmUps: self::DEFAULT_WARM_UPS, setupAfterWarmUp: true, isSuperset: true);

        $this->addBlock($routine, position: 2, exercises: [
            ['name' => 'Arnold Dumbbell Press', 'equipment' => ExerciseEquipment::Dumbbell, 'kg' => 14, 'reps' => 10],
            ['name' => 'Alternate Hammer Curl', 'equipment' => ExerciseEquipment::Dumbbell, 'kg' => 12, 'reps' => 12],
        ], workingSets: 3, workingRest: 90, warmUps: [], isSuperset: true);

        $this->addBlock($routine, position: 3, exercises: [
            ['name' => 'Bent Over Two-Dumbbell Row', 'equipment' => ExerciseEquipment::Dumbbell, 'kg' => 22, 'reps' => 10],
        ], workingSets: 3, workingRest: 90, warmUps: [
            ['percent' => 50, 'reps' => 8],
            ['percent' => 70, 'reps' => 5],
        ]);
    }

    private function seedDropsetFinishers(User $user): void
    {
        $routine = $this->createRoutine($user, 'Dropset Finishers');

        // Set indexes 0–1 single, index 2 dropset.
        $this->addBlock($routine, position: 1, exercises: [
            ['name' => 'Barbell Curl', 'equipment' => ExerciseEquipment::Barbell, 'kg' => 35, 'reps' => 10],
        ], workingSets: 3, workingRest: 90, warmUps: self::DEFAULT_WARM_UPS, dropsets: [
            2 => [35, 25, 15],
        ]);

        // Set index 0 single, index 1 dropset (mixed in one group).
        $this->addBlock($routine, position: 2, exercises: [
            ['name' => 'Dumbbell Bench Press', 'equipment' => ExerciseEquipment::Dumbbell, 'kg' => 26, 'reps' => 10],
        ], workingSets: 2, workingRest: 90, warmUps: [], dropsets: [
            1 => [26, 20, 14],
        ]);

        $this->addBlock($routine, position: 3, exercises: [
            ['name' => 'Barbell Shoulder Press', 'equipment' => ExerciseEquipment::Barbell, 'kg' => 45, 'reps' => 8],
        ], workingSets: 3, workingRest: 120, warmUps: [], setupAfter: true);
    }

    private function createRoutine(User $user, string $name): Routine
    {
        return Routine::create([
            'user_id' => $user->id,
            'name' => $name,
            'deload_weight_factor' => 0.5,
            'deload_reps_factor' => 0.5,
            'deload_every_n' => 3,
        ]);
    }

    /**
     * @param  list<array{name: string, equipment: ExerciseEquipment, kg: float|int, reps: int}>  $exercises
     * @param  list<array{percent: int, reps: int}>  $warmUps
     * @param  array<int, list<float|int>>  $dropsets  set_index => kg weights
     */
    private function addBlock(
        Routine $routine,
        int $position,
        array $exercises,
        int $workingSets,
        int $workingRest,
        array $warmUps = [],
        array $dropsets = [],
        bool $isSuperset = false,
        bool $setupAfter = false,
        bool $setupAfterWarmUp = false,
    ): void {
        if ($isSuperset && count($exercises) !== 2) {
            throw new RuntimeException('Superset blocks require exactly two exercises.');
        }

        if ($isSuperset && $dropsets !== []) {
            throw new RuntimeException('Dropsets are not seeded on supersets.');
        }

        DB::transaction(function () use (
            $routine,
            $position,
            $exercises,
            $workingSets,
            $workingRest,
            $warmUps,
            $dropsets,
            $isSuperset,
            $setupAfter,
            $setupAfterWarmUp,
        ): void {
            $block = RoutineBlock::create([
                'routine_id' => $routine->id,
                'position' => $position,
                'is_superset' => $isSuperset,
                'has_setup_after' => $setupAfter,
                'has_setup_after_warm_up' => $setupAfterWarmUp,
            ]);

            foreach (array_values($exercises) as $index => $exercise) {
                RoutineBlockExercise::create([
                    'routine_block_id' => $block->id,
                    'exercise_id' => $this->resolveExercise($exercise['name'], $exercise['equipment'])->id,
                    'position' => $index + 1,
                    'working_weight_g' => Weight::kgToGrams($exercise['kg']),
                    'prescribed_reps' => $exercise['reps'],
                ]);
            }

            $working = RoutineSetGroup::create([
                'routine_block_id' => $block->id,
                'type' => SetGroupType::Working,
                'set_count' => $workingSets,
                'rest_seconds' => $workingRest,
            ]);

            foreach ($dropsets as $setIndex => $segmentKgs) {
                foreach (array_values($segmentKgs) as $segIndex => $kg) {
                    RoutineDropsetSegment::create([
                        'routine_set_group_id' => $working->id,
                        'set_index' => $setIndex,
                        'position' => $segIndex + 1,
                        'weight_g' => Weight::kgToGrams($kg),
                    ]);
                }
            }

            $warmUpGroup = RoutineSetGroup::create([
                'routine_block_id' => $block->id,
                'type' => SetGroupType::WarmUp,
                'set_count' => count($warmUps),
                'rest_seconds' => $warmUps === [] ? 60 : 60,
            ]);

            foreach (array_values($warmUps) as $stepIndex => $step) {
                RoutineWarmUpStep::create([
                    'routine_set_group_id' => $warmUpGroup->id,
                    'position' => $stepIndex + 1,
                    'percent_of_working' => $step['percent'],
                    'reps' => $step['reps'],
                ]);
            }
        });
    }

    private function resolveExercise(string $name, ExerciseEquipment $equipment): Exercise
    {
        $exercise = Exercise::query()->shared()->where('name', $name)->first();

        if ($exercise !== null) {
            if ($exercise->equipment !== $equipment) {
                $exercise->equipment = $equipment;
                $exercise->save();
            }

            return $exercise;
        }

        throw new RuntimeException(
            "Shared exercise [{$name}] not found. Run ExerciseSeeder (catalog import) before RoutineSeeder."
        );
    }
}
