<?php

use App\Http\Controllers\Exercises\DeleteExerciseController;
use App\Http\Controllers\Exercises\IndexExerciseController;
use App\Http\Controllers\Exercises\ShowExerciseController;
use App\Http\Controllers\Exercises\StoreExerciseController;
use App\Http\Controllers\MuscleGroups\DeleteMuscleGroupController;
use App\Http\Controllers\MuscleGroups\IndexMuscleGroupsController;
use App\Http\Controllers\MuscleGroups\StoreMuscleGroupController;
use App\Http\Controllers\MuscleGroups\UpdateMuscleGroupController;
use App\Http\Controllers\Routines\AddRoutineExerciseController;
use App\Http\Controllers\Routines\CreateRoutineController;
use App\Http\Controllers\Routines\DeleteRoutineController;
use App\Http\Controllers\Routines\IndexRoutineController;
use App\Http\Controllers\Routines\ShowRoutineController;
use App\Http\Controllers\Routines\StoreRoutineController;
use App\Http\Controllers\Routines\UpdateRoutineController;
use App\Http\Controllers\RoutineTypes\CreateRoutineTypeController;
use App\Http\Controllers\RoutineTypes\StoreRoutineTypeController;
use App\Http\Controllers\ShowDashboardController;
use App\Models\Exercise;
use App\Models\MuscleGroup;
use App\Models\RoutineType;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::middleware('auth')->group(function () {

    Route::get('dashboard', ShowDashboardController::class)->name('dashboard');

    Route::prefix('exercises')->group(function () {

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

    Route::prefix('routines')->group(function () {

        Route::get('/', IndexRoutineController::class)
            ->name('routines.index');

        Route::post('/create', StoreRoutineController::class)
            ->name('routines.create');

        Route::get('/create', CreateRoutineController::class)
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

        Route::post('/{routine}/add-exercise/{exercise}', AddRoutineExerciseController::class)
            ->can('addExercise', 'routine')
            ->name('routines.add-exercise');
    });

    Route::prefix('/routine-types')->group(function () {

        Route::post('/create', StoreRoutineTypeController::class)
            ->can('create', RoutineType::class)
            ->name('routine-types.store');

        Route::get('/create', CreateRoutineTypeController::class)
            ->can('create', RoutineType::class)
            ->name('routine-types.create');

    });

    Route::prefix('/muscle-groups')->group(function () {

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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
