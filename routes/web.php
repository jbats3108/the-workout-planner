<?php

use App\Http\Controllers\Exercises\CreateExerciseController;
use App\Http\Controllers\Exercises\DeleteExerciseController;
use App\Http\Controllers\IndexRoutineController;
use App\Http\Controllers\Routines\CreateRoutineController;
use App\Http\Controllers\Routines\DeleteRoutineController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::group(['prefix' => 'exercises', 'middleware' => 'role:admin'], function () {

        Route::post('/create', CreateExerciseController::class)
            ->name('exercises.create');

        Route::delete('/{exercise}', DeleteExerciseController::class)
            ->name('exercises.delete');
    });

    Route::group(['prefix' => 'routines'], function () {

        Route::get('/', IndexRoutineController::class)
            ->name('routines.index');

        Route::post('/create', CreateRoutineController::class)
            ->name('routines.create');

        Route::delete('/{routine}', DeleteRoutineController::class)
            ->can('delete', 'routine')
            ->name('routines.delete');

    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
