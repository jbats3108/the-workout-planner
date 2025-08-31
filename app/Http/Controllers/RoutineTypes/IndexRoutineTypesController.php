<?php

namespace App\Http\Controllers\RoutineTypes;

use App\DataTransferObjects\RoutineTypes\RoutineTypeData;
use App\Http\Controllers\Controller;
use App\Models\RoutineType;
use Illuminate\Http\JsonResponse;

class IndexRoutineTypesController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json(RoutineTypeData::collect(RoutineType::all()));
    }
}
