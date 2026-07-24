<?php

namespace App\MuscleGroups\Http\Controllers;

use App\MuscleGroups\Data\MuscleGroupData;
use App\Shared\Http\Controllers\Controller;
use App\MuscleGroups\Models\MuscleGroup;
use Illuminate\Http\JsonResponse;

class IndexMuscleGroupsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(MuscleGroupData::collect(MuscleGroup::all()));
    }
}
