<?php

use App\Http\Controllers\Exercises\CreateExerciseController;
use App\Http\Controllers\Exercises\DeleteExerciseController;
use App\Http\Controllers\Exercises\IndexExerciseController;
use App\Http\Controllers\Exercises\ShowExerciseController;
use App\Http\Controllers\Routines\AddRoutineExerciseController;
use App\Http\Controllers\Routines\CreateRoutineController;
use App\Http\Controllers\Routines\DeleteRoutineController;
use App\Http\Controllers\Routines\IndexRoutineController;
use App\Http\Controllers\Routines\ShowRoutineController;
use App\Http\Controllers\Routines\UpdateRoutineController;
use App\Models\Exercise;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::prefix('exercises')->group(function () {

        Route::get('/', IndexExerciseController::class)
            ->name('exercises.index');

        Route::get('/{exercise}', ShowExerciseController::class)
            ->name('exercises.show');

        Route::post('/create', CreateExerciseController::class)
            ->can('create', Exercise::class)
            ->name('exercises.create');

        Route::delete('/{exercise}', DeleteExerciseController::class)
            ->can('delete', 'exercise')
            ->name('exercises.delete');
    });

    Route::prefix('routines')->group(function () {

        Route::get('/', IndexRoutineController::class)
            ->name('routines.index');

        Route::post('/create', CreateRoutineController::class)
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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
