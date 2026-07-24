<?php

use App\Http\Controllers\Exercises\DeleteExerciseController;
use App\Http\Controllers\Exercises\IndexExerciseController;
use App\Http\Controllers\Exercises\ShowExerciseController;
use App\Http\Controllers\Exercises\StoreExerciseController;
use App\Http\Controllers\MuscleGroups\DeleteMuscleGroupController;
use App\Http\Controllers\MuscleGroups\IndexMuscleGroupsController;
use App\Http\Controllers\MuscleGroups\StoreMuscleGroupController;
use App\Http\Controllers\MuscleGroups\UpdateMuscleGroupController;
use App\Http\Controllers\Routines\DeleteRoutineController;
use App\Http\Controllers\Routines\IndexRoutineController;
use App\Http\Controllers\Routines\ShowRoutineController;
use App\Http\Controllers\Routines\StoreRoutineController;
use App\Http\Controllers\Routines\UpdateRoutineController;
use App\Http\Controllers\ShowDashboardController;
use App\Http\Controllers\Workouts\StoreWorkoutController;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\Workouts\Workout;
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

    Route::prefix('/workout')->group(function (): void {

        Route::post('/create/{routine}', StoreWorkoutController::class)
            ->can('create', [Workout::class, 'routine'])
            ->name('workout.store');

    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
