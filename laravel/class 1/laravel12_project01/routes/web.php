<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontendController;

/* Route::get('/', function () {
    return view('welcome');
}); */

Route::get('/', [FrontendController::class, 'home']);
Route::get('/about', [FrontendController::class, 'about']);
