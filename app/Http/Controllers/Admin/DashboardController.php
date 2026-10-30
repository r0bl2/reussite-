<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Exercise;
use App\Models\HomeTutorDemand;
use App\Models\Subject;
use App\Models\Summary;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'students'          => User::where('role', 'student')->count(),
            'teachers'          => User::where('role', 'teacher')->count(),
            'subjects'          => Subject::count(),
            'courses'           => Course::count(),
            'courses_pending'   => Course::where('active', false)->count(),
            'summaries'         => Summary::count(),
            'summaries_pending' => Summary::where('active', false)->count(),
            'exercises'         => Exercise::count(),
            'demands_pending'   => HomeTutorDemand::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
