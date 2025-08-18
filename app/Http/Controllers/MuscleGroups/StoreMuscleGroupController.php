<?php

namespace App\Http\Controllers\MuscleGroups;

use App\DataTransferObjects\MuscleGroups\StoreMuscleGroupData;
use App\Http\Controllers\Controller;

class StoreMuscleGroupController extends Controller
{
    public function __invoke(StoreMuscleGroupData $request) {}
}
