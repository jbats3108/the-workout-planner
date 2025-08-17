<?php

namespace App\Http\Controllers\RoutineTypes;

use App\DataTransferObjects\RoutineTypes\StoreRoutineTypeData;
use App\Http\Controllers\Controller;
use App\Models\RoutineType;
use Illuminate\Http\RedirectResponse;

class StoreRoutineTypeController extends Controller
{
    public function __invoke(StoreRoutineTypeData $request): RedirectResponse
    {
        RoutineType::create($request->toArray());

        return redirect(route('dashboard'));
    }
}
