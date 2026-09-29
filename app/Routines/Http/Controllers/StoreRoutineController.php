<?php

namespace App\Routines\Http\Controllers;

use App\ExerciseProfiles\Exceptions\ExerciseProfileNotEditableException;
use App\ExerciseProfiles\Models\ExerciseProfile;
use App\ExerciseProfiles\Services\ExerciseProfileService;
use App\Routines\Data\StoreRoutineData;
use App\Routines\Models\Routine;
use App\Shared\Http\Controllers\Controller;
use App\Users\Enums\ProgressionStyle;
use App\Users\Enums\ProgressiveMidBlock;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class StoreRoutineController extends Controller
{
    public function __invoke(StoreRoutineData $request, ExerciseProfileService $profiles): RedirectResponse
    {
        $profile = ExerciseProfile::query()->findOrFail($request->defaultExerciseProfileId);
        try {
            $profiles->assertSelectable($request->user, $profile);
        } catch (ExerciseProfileNotEditableException $exception) {
            throw ValidationException::withMessages(['default_exercise_profile_id' => $exception->getMessage()]);
        }

        $routine = Routine::create([
            'user_id' => $request->user->id,
            'name' => $request->name,
            'default_exercise_profile_id' => $profile->id,
            'deload_every_n' => $request->deloadEveryN ?? (int) $request->user->deload_every_n_default,
            'progression_style' => $request->progressionStyle
                ?? $request->user->progression_style_default
                ?? ProgressionStyle::StraightSets,
            'progressive_mid_block' => $request->progressiveMidBlock
                ?? $request->user->progressive_mid_block_default
                ?? ProgressiveMidBlock::Ask,
        ]);

        return redirect(route('routines.edit', $routine))->with('success', 'Routine has been created.');
    }
}
