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
Route::get('/students', [StudentController::class, 'index'])->name('students'); // এখানে নাম যুক্ত করুন


/* Route::get('/students/create', function () {
    return view('backend.students.create');
}); */


// Students create route
Route::get('/students/create', function () {
    return view('backend.students.create');
})->name('students.create');
