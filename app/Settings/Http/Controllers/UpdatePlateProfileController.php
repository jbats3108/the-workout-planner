<?php

namespace App\Settings\Http\Controllers;

use App\Shared\Http\Controllers\Controller;
use App\Users\Data\SyncPlateProfilePlateData;
use App\Users\Data\UpsertPlateProfileData;
use App\Users\Models\User;
use App\Users\Services\PlateProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdatePlateProfileController extends Controller
{
    public function __invoke(
        UpsertPlateProfileData $data,
        Request $request,
        PlateProfileService $profiles,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();

        if ($data->bars->count() < 1) {
            return back()->withErrors(['bars' => 'Add at least one bar.']);
        }

        $denominations = $data->plates->toCollection()
            ->map(static fn (SyncPlateProfilePlateData $plate): int => $plate->denominationG);
        if ($denominations->count() !== $denominations->unique()->count()) {
            return back()->withErrors(['plates' => 'Plate denominations must be unique.']);
        }

        $profiles->upsert($user, $data);

        return redirect()
            ->route('training.edit')
            ->with('success', 'Plate profile saved.');
    }
}
