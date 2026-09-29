<?php

namespace App\Routines\Services;

use App\ExerciseProfiles\Models\ExerciseProfile;
use App\ExerciseProfiles\Services\ExerciseProfileService;
use App\ExerciseProfiles\Support\ExerciseProfileAssignment;
use App\Routines\Data\Editor\SyncBlockExerciseData;
use App\Routines\Data\Editor\SyncRoutineBlockData;
use App\Routines\Data\Editor\SyncWarmUpStepData;
use App\Shared\Support\WarmUpStepSupport;
use App\Users\Models\User;
use InvalidArgumentException;

final readonly class RoutineBlockProfileGuard
{
    public function __construct(
        private ExerciseProfileService $exerciseProfiles,
    ) {}

    public function profileFromId(User $user, ?int $profileId): ?ExerciseProfile
    {
        if ($profileId === null) {
            return null;
        }

        $profile = ExerciseProfile::query()->findOrFail($profileId);
        $this->exerciseProfiles->assertAssignable($user, $profile);

        return $profile;
    }

    /**
     * @param  list<SyncBlockExerciseData>  $exercises
     */
    public function resolveSharedProfile(
        User $user,
        SyncRoutineBlockData $blockData,
        array $exercises,
        bool $isSuperset,
        bool $isCircuit,
    ): ?ExerciseProfile {
        if ($isCircuit) {
            return null;
        }

        $sharedProfile = $this->profileFromId($user, $blockData->sharedProfileId);

        if (! $isSuperset) {
            /** @var SyncBlockExerciseData $single */
            $single = $exercises[0];
            if ($single->exerciseProfileId === null) {
                return null;
            }
        }

        return $sharedProfile;
    }

    /**
     * @param  list<SyncWarmUpStepData>  $steps
     */
    public function assertSharedProfileNotTampered(
        ?ExerciseProfile $sharedProfile,
        SyncRoutineBlockData $blockData,
        array $steps,
    ): void {
        if ($sharedProfile === null) {
            return;
        }

        if (! ExerciseProfileAssignment::assignmentIsCurrent(
            $blockData->sharedProfileFingerprint,
            $sharedProfile->recipe()->sharedFingerprint(),
        )) {
            return;
        }

        if ($this->sharedValuesMatchProfile($blockData, $sharedProfile, $steps)) {
            return;
        }

        throw new InvalidArgumentException('The shared profile values no longer match this block.');
    }

    public function assertExerciseProfileNotTampered(
        ?ExerciseProfile $exerciseProfile,
        SyncBlockExerciseData $exerciseData,
        bool $isSuperset,
    ): void {
        if ($exerciseProfile === null) {
            return;
        }

        if (! ExerciseProfileAssignment::assignmentIsCurrent(
            $exerciseData->exerciseProfileFingerprint,
            ExerciseProfileAssignment::exerciseFingerprint($exerciseProfile, $isSuperset),
        )) {
            return;
        }

        if ($this->exerciseValuesMatchProfile($exerciseData, $exerciseProfile)) {
            return;
        }

        throw new InvalidArgumentException('The exercise profile values no longer match this exercise.');
    }

    /**
     * @param  list<SyncWarmUpStepData>  $steps
     */
    private function sharedValuesMatchProfile(
        SyncRoutineBlockData $blockData,
        ExerciseProfile $profile,
        array $steps,
    ): bool {
        if ($blockData->working->restSeconds !== $profile->working_rest_seconds) {
            return false;
        }

        $warmUpSteps = array_map(
            WarmUpStepSupport::toStorage(...),
            WarmUpStepSupport::normalizeList(array_values(array_map(
                static fn (SyncWarmUpStepData $step): array => [
                    'mode' => $step->mode->value,
                    'percent' => $step->percent,
                    'weight_kg' => $step->weightKg,
                    'reps' => $step->reps,
                ],
                $steps,
            ))),
        )
            |> array_values(...);

        return $warmUpSteps === $profile->warmUpStepList();
    }

    private function exerciseValuesMatchProfile(SyncBlockExerciseData $data, ExerciseProfile $profile): bool
    {
        if ($data->prescribedReps !== $profile->target_reps) {
            return false;
        }

        $profileFloorIsDerived = $profile->floor_override === null;

        if ($data->floorIsDerived !== $profileFloorIsDerived) {
            return false;
        }

        if ($data->floorIsDerived) {
            return true;
        }

        return $data->achievementFloor === $profile->floor_override;
    }
}
