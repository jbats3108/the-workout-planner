<?php

use App\Http\Controllers\CreateExerciseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Home');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::post('/exercises/create', CreateExerciseController::class)
        ->middleware('role:admin')
        ->name('exercises.create');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
