<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\SummaryController; 
use App\Http\Controllers\Api\ExerciseController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\HomeTutorDemandController;
use App\Http\Controllers\Api\AdConfigController;

/*
|--------------------------------------------------------------------------
| AUTH (Routes Publiques)
|--------------------------------------------------------------------------
*/
Route::post('/register', [AuthController::class, 'registerApi']);
Route::post('/loginApi', [AuthController::class, 'loginApi']);

/*
|--------------------------------------------------------------------------
| ROUTES PROTÉGÉES (Utilisateurs connectés via Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | TEACHER PROFILE
    |--------------------------------------------------------------------------
    */
    // 📇 Pour l'Enseignant : Récupérer les informations de son profil connecté
    Route::get('/teacher/profile', [AuthController::class, 'getTeacherProfile']);

    /*
    |--------------------------------------------------------------------------
    | COURSES
    |--------------------------------------------------------------------------
    */
    Route::post('/courses', [CourseController::class, 'store']);
    Route::get('/courses', [CourseController::class, 'index']);
    Route::put('/courses/{course}/active', [CourseController::class, 'toggleActive']);
    Route::put('/courses/{id}', [CourseController::class, 'update']);
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
    Route::get('/courses/{id}/download', [CourseController::class, 'download']);

    /*
    |--------------------------------------------------------------------------
    | SUMMARIES (RÉSUMÉS DE COURS)
    |--------------------------------------------------------------------------
    */
    Route::post('/summaries', [SummaryController::class, 'store']);
    Route::get('/summaries', [SummaryController::class, 'index']);
    Route::put('/summaries/{id}/active', [SummaryController::class, 'toggleActive']);
    Route::put('/summaries/{id}', [SummaryController::class, 'update']); // Utilise POST pour supporter multipart/form-data (_method=PUT)
    Route::delete('/summaries/{id}', [SummaryController::class, 'destroy']);
    Route::get('/summaries/{id}/download', [SummaryController::class, 'download']);

    /*
    |--------------------------------------------------------------------------
    | EXERCISES
    |--------------------------------------------------------------------------
    */
    Route::post('/exercises', [ExerciseController::class, 'store']);
    Route::get('/exercises', [ExerciseController::class, 'index']);
    Route::delete('/exercises/{id}', [ExerciseController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | MATIÈRES (SUBJECTS)
    |--------------------------------------------------------------------------
    */
    // Consultations des matières (Tous les utilisateurs connectés)
    Route::get('/subjects', [SubjectController::class, 'index']);

    // Gestion des matières (Réservé à l'Admin via vérification dans le contrôleur)
    Route::post('/subjects', [SubjectController::class, 'store']);
    Route::put('/subjects/{id}', [SubjectController::class, 'update']);
    Route::delete('/subjects/{id}', [SubjectController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | REPETITIONS A DOMICILE (HOME TUTOR DEMANDS)
    |--------------------------------------------------------------------------
    */
    // 📇 Pour l'Enseignant : Soumettre le formulaire de candidature
    Route::post('/teacher/home-tutor-demand', [HomeTutorDemandController::class, 'store']);

    // 🟢 Pour l'Enseignant : Consulter le statut de sa propre demande
    Route::get('/teacher/my-demand', [HomeTutorDemandController::class, 'getMyDemandStatus']);

    // 👑 Pour l'Admin : Récupérer les demandes en attente de validation
    Route::get('/admin/home-tutor-demands', [HomeTutorDemandController::class, 'indexAdmin']);
    
    // 👑 Pour l'Admin : Accepter ou refuser une demande spécifique
    Route::put('/admin/home-tutor-demands/{id}/validate', [HomeTutorDemandController::class, 'validateDemand']);

    // 👑 Route Admin : Pour collecter l'historique complet des demandes traitées
    Route::get('/admin/home-tutor-history', [HomeTutorDemandController::class, 'indexHistoryAdmin']);

    // 👥 Route Publique/Étudiant : Pour afficher la liste finale des répétiteurs validés
    Route::get('/public/approved-tutors', [HomeTutorDemandController::class, 'indexApprovedPublic']);

    /*
    |--------------------------------------------------------------------------
    | PUBLICITÉS (ADS CONFIGURATION)
    |--------------------------------------------------------------------------
    */
    // 📢 Route pour récupérer les IDs et le statut d'affichage des publicités
    Route::get('/ad-config', [AdConfigController::class, 'index']);
   
});