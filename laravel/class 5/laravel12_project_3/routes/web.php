<?php

use App\Http\Controllers\StudentController;
<<<<<<< HEAD
=======
use App\Http\Controllers\DistrictController;
>>>>>>> e7d21a1 (hw districts add)
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
<<<<<<< HEAD
/* Route::get('/dashboard', function () {
    return view('backend.dashboard');
}); --------------------------------------*/
=======

>>>>>>> e7d21a1 (hw districts add)

// Dashboard route
Route::get('/dashboard', function () {
    return view('backend.dashboard');
})->name('dashboard'); // Dashboard এর জন্যও নাম দিন

<<<<<<< HEAD
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
Route::get('/students/{id}/show', [StudentController::class, 'show'])->name('students.show'); 
Route::post('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy'); 


/* Route::get('/students/create', function () {
    return view('backend.students.create');
}); */


=======
// ==========================================
// Students Routes
// ==========================================
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
Route::get('/students/{id}/edit', [StudentController::class, 'edit'])->name('students.edit');
Route::post('/students/{id}/update', [StudentController::class, 'update'])->name('students.update');
Route::get('/students/{id}/show', [StudentController::class, 'show'])->name('students.show');
Route::post('/students/{id}', [StudentController::class, 'destroy'])->name('students.destroy');

// ==========================================
// Districts Routes 
// ==========================================
Route::get('/districts', [DistrictController::class, 'index'])->name('districts.index');
Route::get('/districts/create', [DistrictController::class, 'create'])->name('districts.create');
Route::post('/districts', [DistrictController::class, 'store'])->name('districts.store');
Route::get('/districts/{id}/show', [DistrictController::class, 'show'])->name('districts.show');
Route::get('/districts/{id}/edit', [DistrictController::class, 'edit'])->name('districts.edit');
Route::post('/districts/{id}/update', [DistrictController::class, 'update'])->name('districts.update');
Route::post('/districts/{id}', [DistrictController::class, 'destroy'])->name('districts.destroy');
>>>>>>> e7d21a1 (hw districts add)
