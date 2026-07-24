<?php

namespace App\Settings\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Users\Data\UpdateTrainingDefaultsData;
use App\Users\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TrainingDefaultsController extends Controller
{
    public function edit(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('settings/Training', [
            'warm_up_steps_default' => $user->resolvedWarmUpStepsDefault(),
            'using_app_fallback' => $user->warm_up_steps_default === null,
        ]);
    }

    public function update(UpdateTrainingDefaultsData $data, Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();

        $steps = $data->warmUpStepsDefault === null
            ? []
            : array_values(array_map(
                static fn ($step): array => [
                    'percent' => min(100, max(1, $step->percent)),
                    'reps' => min(100, max(1, $step->reps)),
                ],
                $data->warmUpStepsDefault->all()
            ));

        $user->warm_up_steps_default = $steps;
        $user->save();

        return redirect()
            ->route('training.edit')
            ->with('success', 'Training defaults saved.');
    }

    public function reset(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->warm_up_steps_default = null;
        $user->save();

        return redirect()
            ->route('training.edit')
            ->with('success', 'Warm-up defaults reset to app fallback.');
    }
}
