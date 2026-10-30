<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exercise;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExerciseController extends Controller
{
    public function index(Request $request)
    {
        $query = Exercise::with(['teacher', 'subject']);
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }
        $exercises = $query->latest()->paginate(15);
        $exercises->appends($request->query());
        $subjects = Subject::orderBy('type_lycee')->orderBy('name')->get();

        return view('admin.exercises.index', compact('exercises', 'subjects'));
    }

    public function create()
    {
        return view('admin.exercises.form', [
            'exercise' => new Exercise(),
            'subjects' => Subject::orderBy('type_lycee')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
            'niveau'     => 'nullable|string',
            'classe'     => 'nullable|string',
            'file'       => 'required|file|max:51200',
            'correction' => 'nullable|file|max:51200',
        ]);

        $data['file_path'] = $request->file('file')->store('exercises', 'public');
        if ($request->hasFile('correction')) {
            $data['correction_path'] = $request->file('correction')->store('exercises/corrections', 'public');
        }
        unset($data['file'], $data['correction']);
        $data['teacher_id'] = null;
        $data['active'] = true;

        Exercise::create($data);

        return redirect()->route('exercises.index')->with('success', 'Exercice créé.');
    }

    public function edit(Exercise $exercise)
    {
        return view('admin.exercises.form', [
            'exercise' => $exercise,
            'subjects' => Subject::orderBy('type_lycee')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Exercise $exercise)
    {
        $data = $request->validate([
            'title'      => 'required|string|max:255',
            'subject_id' => 'required|exists:subjects,id',
           'niveau'     => 'required|string',
            'classe'     => 'nullable|string',
            'file'       => 'nullable|file|max:51200',
            'correction' => 'nullable|file|max:51200',
        ]);

        if ($request->hasFile('file')) {
            $this->remove($exercise->file_path);
            $data['file_path'] = $request->file('file')->store('exercises', 'public');
        }
        if ($request->hasFile('correction')) {
            $this->remove($exercise->correction_path);
            $data['correction_path'] = $request->file('correction')->store('exercises/corrections', 'public');
        }
        unset($data['file'], $data['correction']);

        $exercise->update($data);

        return redirect()->route('exercises.index')->with('success', 'Exercice modifié.');
    }

    public function toggle(Exercise $exercise)
    {
        $exercise->active = !$exercise->active;
        $exercise->save();

        return back()->with('success', $exercise->active ? 'Exercice activé.' : 'Exercice désactivé.');
    }

    public function download(Exercise $exercise, $type = 'file')
    {
        $rel = $type === 'correction' ? $exercise->correction_path : $exercise->file_path;
        $path = $rel ? storage_path('app/public/' . $rel) : null;
        abort_unless($path && file_exists($path), 404, 'Fichier introuvable');

        return response()->download($path);
    }

    public function destroy(Exercise $exercise)
    {
        $this->remove($exercise->file_path);
        $this->remove($exercise->correction_path);
        $exercise->delete();

        return back()->with('success', 'Exercice supprimé.');
    }

    private function remove(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
