<?php

use App\Admin\Http\Controllers\IndexAdminExercisesController;
use App\Admin\Http\Controllers\IndexAdminMuscleGroupsController;
use App\Admin\Http\Controllers\IndexAdminUsersController;
use App\Admin\Http\Controllers\ShowAdminController;
use App\Dashboard\Http\Controllers\ShowDashboardController;
use App\Exercises\Http\Controllers\DeleteExerciseController;
use App\Exercises\Http\Controllers\IndexExerciseController;
use App\Exercises\Http\Controllers\ShowExerciseController;
use App\Exercises\Http\Controllers\StoreExerciseController;
use App\Exercises\Models\Exercise;
use App\MuscleGroups\Http\Controllers\DeleteMuscleGroupController;
use App\MuscleGroups\Http\Controllers\IndexMuscleGroupsController;
use App\MuscleGroups\Http\Controllers\StoreMuscleGroupController;
use App\MuscleGroups\Http\Controllers\UpdateMuscleGroupController;
use App\MuscleGroups\Models\MuscleGroup;
use App\Routines\Http\Controllers\CreateRoutineController;
use App\Routines\Http\Controllers\DeleteRoutineController;
use App\Routines\Http\Controllers\EditRoutineController;
use App\Routines\Http\Controllers\IndexRoutineController;
use App\Routines\Http\Controllers\ShowRoutineController;
use App\Routines\Http\Controllers\StoreRoutineController;
use App\Routines\Http\Controllers\UpdateRoutineController;
use App\Workouts\Http\Controllers\AddWorkingSetController;
use App\Workouts\Http\Controllers\ApplyProgressionBumpsController;
use App\Workouts\Http\Controllers\CompleteWorkoutSetController;
use App\Workouts\Http\Controllers\FinishWorkoutController;
use App\Workouts\Http\Controllers\PlayWorkoutController;
use App\Workouts\Http\Controllers\RemoveWorkingSetController;
use App\Workouts\Http\Controllers\ShowProgressionController;
use App\Workouts\Http\Controllers\SkipProgressionController;
use App\Workouts\Http\Controllers\StoreWorkoutController;
use App\Workouts\Models\Workout;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', fn () => Inertia::render('Home'))->name('home');

Route::middleware('auth')->group(function (): void {

    Route::get('dashboard', ShowDashboardController::class)->name('dashboard');

    Route::prefix('exercises')->group(function (): void {

        Route::get('/', IndexExerciseController::class)
            ->name('exercises.index');

        Route::get('/{exercise}', ShowExerciseController::class)
            ->name('exercises.show');

        Route::post('/create', StoreExerciseController::class)
            ->can('create', Exercise::class)
            ->name('exercises.store');

        Route::delete('/{exercise}', DeleteExerciseController::class)
            ->can('delete', 'exercise')
            ->name('exercises.delete');
    });

    Route::prefix('routines')->group(function (): void {

        Route::get('/', IndexRoutineController::class)
            ->name('routines.index');

        Route::get('/create', CreateRoutineController::class)
            ->name('routines.create');

        Route::post('/create', StoreRoutineController::class)
            ->name('routines.store');

        Route::get('/{routine}/edit', EditRoutineController::class)
            ->can('view', 'routine')
            ->name('routines.edit');

        Route::get('/{routine}', ShowRoutineController::class)
            ->can('view', 'routine')
            ->name('routines.show');

        Route::delete('/{routine}', DeleteRoutineController::class)
            ->can('delete', 'routine')
            ->name('routines.delete');

        Route::put('/{routine}', UpdateRoutineController::class)
            ->can('update', 'routine')
            ->name('routines.update');
    });

    Route::prefix('/muscle-groups')->group(function (): void {

        Route::post('/create', StoreMuscleGroupController::class)
            ->can('create', MuscleGroup::class)
            ->name('muscle-groups.store');

        Route::get('/', IndexMuscleGroupsController::class)
            ->name('muscle-groups.index');

        Route::delete('/{muscleGroup}', DeleteMuscleGroupController::class)
            ->can('delete', MuscleGroup::class)
            ->name('muscle-groups.delete');

        Route::put('/{muscleGroup}', UpdateMuscleGroupController::class)
            ->can('update', MuscleGroup::class)
            ->name('muscle-groups.update');
    });

    Route::prefix('/workouts')->group(function (): void {
        Route::post('/create/{routine}', StoreWorkoutController::class)
            ->can('create', [Workout::class, 'routine'])
            ->name('workouts.store');

        Route::get('/{workout}/play', PlayWorkoutController::class)
            ->can('view', 'workout')
            ->name('workouts.play');

        Route::post('/{workout}/sets/{set}', CompleteWorkoutSetController::class)
            ->can('update', 'workout')
            ->name('workouts.sets.complete');

        Route::post('/{workout}/blocks/{block}/working-sets', AddWorkingSetController::class)
            ->can('update', 'workout')
            ->name('workouts.working-sets.add');

        Route::delete('/{workout}/sets/{set}', RemoveWorkingSetController::class)
            ->can('update', 'workout')
            ->name('workouts.sets.remove');

        Route::post('/{workout}/finish', FinishWorkoutController::class)
            ->can('update', 'workout')
            ->name('workouts.finish');

        Route::get('/{workout}/progression', ShowProgressionController::class)
            ->can('view', 'workout')
            ->name('workouts.progression');

        Route::post('/{workout}/progression', ApplyProgressionBumpsController::class)
            ->can('applyProgression', 'workout')
            ->name('workouts.progression.apply');

        Route::post('/{workout}/progression/skip', SkipProgressionController::class)
            ->can('applyProgression', 'workout')
            ->name('workouts.progression.skip');
    });

    Route::prefix('admin')->middleware('role:admin')->group(function (): void {
        Route::get('/', ShowAdminController::class)->name('admin.index');
        Route::get('/exercises', IndexAdminExercisesController::class)->name('admin.exercises');
        Route::get('/muscle-groups', IndexAdminMuscleGroupsController::class)->name('admin.muscle-groups');
        Route::get('/users', IndexAdminUsersController::class)->name('admin.users');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
