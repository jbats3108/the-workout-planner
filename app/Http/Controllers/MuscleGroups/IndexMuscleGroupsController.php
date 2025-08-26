<?php

namespace App\Http\Controllers\MuscleGroups;

use App\DataTransferObjects\MuscleGroups\MuscleGroupData;
use App\Http\Controllers\Controller;
use App\Models\MuscleGroup;
use Illuminate\Http\JsonResponse;

class IndexMuscleGroupsController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(MuscleGroupData::collect(MuscleGroup::all()));
    }
}
