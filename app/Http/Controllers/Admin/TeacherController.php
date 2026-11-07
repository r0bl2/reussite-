<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('teachers')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->where('users.role', 'teacher')
            ->select('teachers.*', 'users.username', 'users.email', 'users.telephone as user_phone');

        if ($request->filled('q')) {
            $q = '%' . $request->q . '%';
            $query->where(function ($w) use ($q) {
                $w->where('users.username', 'like', $q)
                  ->orWhere('users.email', 'like', $q)
                  ->orWhere('teachers.telephone', 'like', $q);
            });
        }
        if ($request->filled('type_lycee')) {
            $query->where('teachers.type_lycee', $request->type_lycee);
        }

        $teachers = $query->orderByDesc('teachers.created_at')->paginate(10)->withQueryString();

        $teacherIds = collect($teachers->items())->pluck('id')->all();
        $userIds    = collect($teachers->items())->pluck('user_id')->all();

        $courses   = DB::table('courses')->whereIn('teacher_id', $teacherIds)->selectRaw('teacher_id, count(*) as c')->groupBy('teacher_id')->pluck('c', 'teacher_id');
        $summaries = DB::table('summaries')->whereIn('teacher_id', $teacherIds)->selectRaw('teacher_id, count(*) as c')->groupBy('teacher_id')->pluck('c', 'teacher_id');
        $exercises = DB::table('exercises')->whereIn('teacher_id', $userIds)->selectRaw('teacher_id, count(*) as c')->groupBy('teacher_id')->pluck('c', 'teacher_id');

        return view('admin.teachers.index', compact('teachers', 'courses', 'summaries', 'exercises'));
    }
}
