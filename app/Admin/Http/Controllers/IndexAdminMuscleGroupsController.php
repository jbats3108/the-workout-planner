<?php

namespace App\Admin\Http\Controllers;

use App\MuscleGroups\Models\MuscleGroup;
use App\Shared\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class IndexAdminMuscleGroupsController extends Controller
{
    public function __invoke(): Response
    {
        $muscleGroups = MuscleGroup::query()
            ->orderBy('name')
            ->get()
            ->map(fn (MuscleGroup $group): array => [
                'id' => $group->id,
                'name' => $group->getName(),
                'slug' => $group->getSlug(),
            ]);

        return Inertia::render('admin/MuscleGroups', [
            'muscle_groups' => $muscleGroups,
        ]);
    }
}
