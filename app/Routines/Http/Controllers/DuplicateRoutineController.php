<?php

namespace App\Routines\Http\Controllers;

use App\Routines\Models\Routine;
use App\Routines\Services\RoutineDuplicator;
use App\Shared\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class DuplicateRoutineController extends Controller
{
    public function __invoke(Request $request, Routine $routine, RoutineDuplicator $duplicator): RedirectResponse
    {
        try {
            $copy = $duplicator->duplicate($routine, $request->user());
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['routine' => $e->getMessage()]);
        }

        return redirect()
            ->route('routines.edit', $copy)
            ->with('success', 'Routine duplicated.');
    }
}
