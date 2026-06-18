<?php

use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
/* Route::get('/dashboard', function () {
    return view('backend.dashboard');
}); --------------------------------------*/

// Dashboard route
Route::get('/dashboard', function () {
    return view('backend.dashboard');
})->name('dashboard'); // Dashboard এর জন্যও নাম দিন

/* Route::get('/students', function () {
    return view('backend.students.index');
}); ------------------------------------------*/

/* Route::get('students', [StudentController::class, 'index']); */
// Students index route
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
 
// Route::get('/students', [StudentController::class, 'store'])->name('students.store'); 
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::get('/students/create', [StudentController::class, 'create'])->name('students.create'); 
Route::get('/students/{id}/edit', [StudentController::class, 'edit'])->name('students.edit'); 
Route::post('/students/{id}/update', [StudentController::class, 'update'])->name('students.update'); 
Route::post('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy'); 


/* Route::get('/students/create', function () {
    return view('backend.students.create');
}); */


