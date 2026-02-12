<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\BookController;


// Inscription utilisateur

Route::post('/register', [AuthController::class, 'registerApi'])->name('register.api');
Route::get('/register', [AuthController::class, 'registerApi'])->name('register.api');

// connexion utilisateur
Route::post('/loginApi', [AuthController::class, 'loginApi']);
Route::get('/loginApi', [AuthController::class, 'loginApi']);



 /*
|--------------------------------------------------------------------------
| COURSES – PUBLIC (étudiants, parents)
|--------------------------------------------------------------------------
*/
//Route::get('/courses', [CourseController::class, 'index']);
//Route::get('/courses/{id}/download', [CourseController::class, 'download']);


//Route::middleware('auth:sanctum')->post('/courses', [CourseController::class, 'store']);
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::put('/courses/{course}/active', [CourseController::class, 'toggleActive']);
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
});



