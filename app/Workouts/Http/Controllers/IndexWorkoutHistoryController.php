<?php

namespace App\Workouts\Http\Controllers;

use App\Routines\Models\Routine;
use App\Shared\Http\Controllers\Controller;
use App\Users\Models\User;
use App\Workouts\Data\History\HistoryIndexPageData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IndexWorkoutHistoryController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        $routineSlug = $request->string('routine')->toString() ?: null;
        $routineId = null;

        if ($routineSlug !== null) {
            $routineId = Routine::query()
                ->where('user_id', $user->id)
                ->where('slug', $routineSlug)
                ->value('id');
        }

        return Inertia::render('history/Index', [
            'history' => HistoryIndexPageData::forUser($user, $routineId !== null ? (int) $routineId : null, $routineSlug),
        ]);
    }
}
