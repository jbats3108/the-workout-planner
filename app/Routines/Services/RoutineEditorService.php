<?php

namespace App\Routines\Services;

use App\ExerciseProfiles\Models\ExerciseProfile;
use App\ExerciseProfiles\Services\ExerciseProfileService;
use App\Routines\Data\Editor\SyncRoutineBlockData;
use App\Routines\Data\Editor\SyncRoutineData;
use App\Routines\Exceptions\RoutineStaleException;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Users\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RoutineEditorService
{
    public function __construct(
        private readonly ExerciseProfileService $exerciseProfiles,
        private readonly RoutineBlockWriter $blockWriter,
    ) {}

    public function sync(Routine $routine, SyncRoutineData $data): Routine
    {
        return DB::transaction(function () use ($routine, $data): Routine {
            $locked = Routine::query()->whereKey($routine->id)->lockForUpdate()->firstOrFail();
            $locked->loadMissing('user');

            if ($data->expectedUpdatedAt !== null) {
                $expected = Carbon::parse($data->expectedUpdatedAt);
                if ($locked->updated_at === null || $locked->updated_at->getTimestamp() !== $expected->getTimestamp()) {
                    throw new RoutineStaleException;
                }
            }

            $defaultProfile = $this->selectableProfileFromId($locked->user, $data->defaultExerciseProfileId);

            $locked->update([
                'name' => $data->name,
                'deload_every_n' => $data->deloadEveryN ?? $locked->deload_every_n,
                'progression_style' => $data->progressionStyle ?? $locked->progression_style,
                'progressive_mid_block' => $data->progressiveMidBlock ?? $locked->progressive_mid_block,
                'default_exercise_profile_id' => $defaultProfile === null
                    ? $locked->default_exercise_profile_id
                    : $defaultProfile->id,
            ]);

            $locked->blocks()->each(function (RoutineBlock $block): void {
                $block->delete();
            });

            $blocks = ($data->blocks ?? [])
                |> iterator_to_array(...)
                |> array_values(...);
            $lastIndex = count($blocks) - 1;

            foreach ($blocks as $index => $blockData) {
                /** @var SyncRoutineBlockData $blockData */
                $this->blockWriter->create($locked, $index + 1, $blockData, $index === $lastIndex);
            }

            return $locked->fresh(Routine::EDITOR_STRUCTURE) ?? $locked;
        });
    }

    private function profileFromId(User $user, ?int $profileId): ?ExerciseProfile
    {
        if ($profileId === null) {
            return null;
        }

        $profile = ExerciseProfile::query()->findOrFail($profileId);
        $this->exerciseProfiles->assertAssignable($user, $profile);

        return $profile;
    }

    private function selectableProfileFromId(User $user, ?int $profileId): ?ExerciseProfile
    {
        if ($profileId === null) {
            return null;
        }

        $profile = $this->profileFromId($user, $profileId);
        $this->exerciseProfiles->assertSelectable($user, $profile);

        return $profile;
    }
}
