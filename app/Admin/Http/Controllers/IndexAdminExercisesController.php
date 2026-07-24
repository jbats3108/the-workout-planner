<?php

namespace App\Admin\Http\Controllers;

use App\Exercises\Models\Exercise;
use App\MuscleGroups\Models\MuscleGroup;
use App\Shared\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class IndexAdminExercisesController extends Controller
{
    public function __invoke(): Response
    {
        $exercises = Exercise::query()
            ->shared()
            ->with(['primaryMuscleGroup', 'secondaryMuscleGroup'])
            ->orderBy('name')
            ->get()
            ->map(fn (Exercise $exercise): array => [
                'id' => $exercise->id,
                'name' => $exercise->getName(),
                'slug' => $exercise->getSlug(),
                'primary_muscle_group' => $exercise->primaryMuscleGroup->getName(),
                'primary_muscle_group_slug' => $exercise->primaryMuscleGroup->getSlug(),
                'secondary_muscle_group' => $exercise->secondaryMuscleGroup?->getName(),
                'secondary_muscle_group_slug' => $exercise->secondaryMuscleGroup?->getSlug(),
            ]);

        $muscleGroups = MuscleGroup::query()
            ->orderBy('name')
            ->get()
            ->map(fn (MuscleGroup $group): array => [
                'name' => $group->getName(),
                'slug' => $group->getSlug(),
            ]);

        return Inertia::render('admin/Exercises', [
            'exercises' => $exercises,
            'muscle_groups' => $muscleGroups,
        ]);
    }
}
