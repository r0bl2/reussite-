<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['teacher.user', 'subject']);

        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        $courses = $query->latest()->paginate(15);
        $courses->appends($request->query());
        $subjects = Subject::orderBy('name')->get();

        return view('admin.courses.index', compact('courses', 'subjects'));
    }

    public function create()
    {
        return view('admin.courses.form', [
            'course'   => new Course(),
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'file'        => 'required|file|max:51200',
            'subject_id'  => 'required|exists:subjects,id',
            'level'       => 'nullable|string',
            'class'       => 'nullable|string',
        ]);

        $data['file_path'] = $request->file('file')->store('courses', 'public');
        unset($data['file']);
        $data['teacher_id'] = null;
        $data['active']     = true;

        Course::create($data);

        return redirect()->route('courses.index')->with('success', 'Cours créé.');
    }

    public function edit(Course $course)
    {
        return view('admin.courses.form', [
            'course'   => $course,
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Course $course)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'file'        => 'nullable|file|max:51200',
            'subject_id'  => 'required|exists:subjects,id',
            'level'       => 'nullable|string',
            'class'       => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {
            if ($course->file_path && Storage::disk('public')->exists($course->file_path)) {
                Storage::disk('public')->delete($course->file_path);
            }
            $data['file_path'] = $request->file('file')->store('courses', 'public');
        }
        unset($data['file']);

        $course->update($data);

        return redirect()->route('courses.index')->with('success', 'Cours modifié.');
    }

    public function toggle(Course $course)
    {
        $course->active = !$course->active;
        $course->save();

        return back()->with('success', $course->active ? 'Cours activé.' : 'Cours désactivé.');
    }

    public function download(Course $course)
    {
        $path = storage_path('app/public/' . $course->file_path);
        abort_unless($course->file_path && file_exists($path), 404, 'Fichier introuvable');

        return response()->download($path);
    }

    public function destroy(Course $course)
    {
        if ($course->file_path && Storage::disk('public')->exists($course->file_path)) {
            Storage::disk('public')->delete($course->file_path);
        }
        $course->delete();

        return back()->with('success', 'Cours supprimé.');
    }
}
