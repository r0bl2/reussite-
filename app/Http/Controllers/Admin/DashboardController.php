<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $papers = Schema::hasTable('exam_papers');

        $cards = [
            ['Élèves', DB::table('users')->where('role', 'student')->count(), 'primary'],
            ['Enseignants', DB::table('users')->where('role', 'teacher')->count(), 'success'],
            ['Matières', DB::table('subjects')->count(), 'info'],
            ['Cours', DB::table('courses')->count(), 'warning'],
            ['Résumés', DB::table('summaries')->count(), 'secondary'],
            ['Exercices', DB::table('exercises')->count(), 'dark'],
        ];

        $chart = [
            'labels'  => ['Cours', 'Résumés', 'Exercices', 'Épreuves'],
            'actifs'  => [
                DB::table('courses')->where('active', 1)->count(),
                DB::table('summaries')->where('active', 1)->count(),
                DB::table('exercises')->where('active', 1)->count(),
                $papers ? DB::table('exam_papers')->where('status', 'approved')->count() : 0,
            ],
            'attente' => [
                DB::table('courses')->where('active', 0)->count(),
                DB::table('summaries')->where('active', 0)->count(),
                DB::table('exercises')->where('active', 0)->count(),
                $papers ? DB::table('exam_papers')->where('status', 'pending')->count() : 0,
            ],
        ];

        $demands = [
            DB::table('home_tutor_demands')->where('status', 'pending')->count(),
            DB::table('home_tutor_demands')->where('status', 'approved')->count(),
            DB::table('home_tutor_demands')->where('status', 'rejected')->count(),
        ];

        return view('admin.dashboard', compact('cards', 'chart', 'demands'));
    }
}
