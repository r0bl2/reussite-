<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExamPaperController;
use App\Http\Controllers\Admin\ExerciseController;
use App\Http\Controllers\Admin\HomeTutorDemandController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\SummaryController;
use App\Http\Controllers\Admin\TeacherController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'registerApi'])->name('inscription');

Route::redirect('/', '/dashboard');
Route::redirect('/admin', '/dashboard');

Route::get('/dashboard/login', [AdminAuthController::class, 'showLogin'])->name('admin.login');
Route::post('/dashboard/login', [AdminAuthController::class, 'login'])->name('admin.login.submit');

Route::middleware('admin')->prefix('dashboard')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');

    // Actions spécifiques (toujours AVANT les resources)
    Route::put('courses/{course}/toggle', [CourseController::class, 'toggle'])->name('courses.toggle');
    Route::get('courses/{course}/download', [CourseController::class, 'download'])->name('courses.download');
    Route::get('courses/{course}/preview', [CourseController::class, 'preview'])->name('courses.preview');

    Route::put('summaries/{summary}/toggle', [SummaryController::class, 'toggle'])->name('summaries.toggle');
    Route::get('summaries/{summary}/download', [SummaryController::class, 'download'])->name('summaries.download');
    Route::get('summaries/{summary}/preview', [SummaryController::class, 'preview'])->name('summaries.preview');

    Route::put('exercises/{exercise}/toggle', [ExerciseController::class, 'toggle'])->name('exercises.toggle');
    Route::get('exercises/{exercise}/download/{type?}', [ExerciseController::class, 'download'])->name('exercises.download');
    Route::get('exercises/{exercise}/preview/{type}', [ExerciseController::class, 'preview'])->name('exercises.preview');

    Route::put('exam-papers/{exam_paper}/approve', [ExamPaperController::class, 'approve'])->name('exam-papers.approve');
    Route::put('exam-papers/{exam_paper}/reject', [ExamPaperController::class, 'reject'])->name('exam-papers.reject');
    Route::get('exam-papers/{exam_paper}/download', [ExamPaperController::class, 'download'])->name('exam-papers.download');
    Route::get('exam-papers/{exam_paper}/preview', [ExamPaperController::class, 'preview'])->name('exam-papers.preview');

    Route::get('home-tutor-history', [HomeTutorDemandController::class, 'history'])->name('home-tutor-demands.history');
    Route::put('home-tutor-demands/{home_tutor_demand}/validate', [HomeTutorDemandController::class, 'approve'])->name('home-tutor-demands.validate');
    Route::put('home-tutor-demands/{home_tutor_demand}/reject', [HomeTutorDemandController::class, 'reject'])->name('home-tutor-demands.reject');
    Route::get('home-tutor-demands/{home_tutor_demand}/file/{field}', [HomeTutorDemandController::class, 'file'])->name('home-tutor-demands.file');

    // Pédagogie et contenus
    Route::resource('subjects', SubjectController::class)->except(['show']);
    Route::resource('courses', CourseController::class);
    Route::resource('exercises', ExerciseController::class);
    Route::resource('summaries', SummaryController::class);
    Route::resource('exam-papers', ExamPaperController::class)->only(['index', 'create', 'store', 'show', 'destroy']);

    // Communauté
    Route::resource('teachers', TeacherController::class)->only(['index']);
    Route::resource('home-tutor-demands', HomeTutorDemandController::class)->only(['index', 'show']);

    // Communication
    Route::resource('news', NewsController::class)->except(['show']);
    Route::resource('banners', BannerController::class)->except(['show']);
});
