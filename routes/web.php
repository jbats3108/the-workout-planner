<?php

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
use App\Routines\Http\Controllers\DeleteRoutineController;
use App\Routines\Http\Controllers\EditRoutineController;
use App\Routines\Http\Controllers\IndexRoutineController;
use App\Routines\Http\Controllers\ShowRoutineController;
use App\Routines\Http\Controllers\StoreRoutineController;
use App\Routines\Http\Controllers\UpdateRoutineController;
use App\Workouts\Http\Controllers\CompleteWorkoutSetController;
use App\Workouts\Http\Controllers\FinishWorkoutController;
use App\Workouts\Http\Controllers\PlayWorkoutController;
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

        Route::post('/create', StoreRoutineController::class)
            ->name('routines.create');

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

        Route::post('/{workout}/finish', FinishWorkoutController::class)
            ->can('update', 'workout')
            ->name('workouts.finish');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
