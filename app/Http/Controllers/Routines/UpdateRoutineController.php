<?php

namespace App\Http\Controllers\Routines;

use App\DataTransferObjects\UpdateRoutineData;
use App\Http\Controllers\Controller;
use App\Models\Routine;
use Illuminate\Http\RedirectResponse;

class UpdateRoutineController extends Controller
{
    public function __invoke(UpdateRoutineData $request, Routine $routine): RedirectResponse
    {
        /** @var array<string,mixed> $updatePayload */
        $updatePayload = array_filter(
            array_merge(
                $routine->toArray(),
                $request->toArray()
            )
        );
        $routine->update($updatePayload);

        return redirect()->route('routines.show', $routine);
    }
}
