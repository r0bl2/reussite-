<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\StudentController;
use App\Models\Student;;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


/*Route::get('/students', [StudentController::class , 'index']);
Route::get('/lesStudents', [StudentController::class, 'lesStudents'])->name('student.list');
*/

Route::post('/register', [AuthController::class, 'registerApi'])->name('inscription ');
