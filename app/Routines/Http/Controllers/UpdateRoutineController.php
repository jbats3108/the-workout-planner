<?php

namespace App\Routines\Http\Controllers;

use App\Routines\Data\Editor\SyncRoutineData;
use App\Routines\Models\Routine;
use App\Routines\Services\RoutineEditorService;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class UpdateRoutineController extends Controller
{
    public function __invoke(SyncRoutineData $data, Routine $routine, RoutineEditorService $editor): RedirectResponse
    {
        try {
            $editor->sync($routine, $data);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['blocks' => $e->getMessage()]);
        }

        return redirect()
            ->route('dashboard')
            ->with('success', 'Routine saved.');
    }
}
