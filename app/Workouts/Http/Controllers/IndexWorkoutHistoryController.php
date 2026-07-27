<?php

namespace App\Workouts\Http\Controllers;

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

        $routineId = $request->integer('routine') ?: null;

        return Inertia::render('history/Index', [
            'history' => HistoryIndexPageData::forUser($user, $routineId),
        ]);
    }
}
