<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    private const TYPES = [
        'college'          => 'Collège',
        'lycee_moderne'    => 'Lycée moderne',
        'lycee_technique'  => 'Lycée technique',
    ];

    public function index(Request $request)
    {
        $query = Subject::query();

        if ($request->filled('q')) {
            $query->where('name', 'like', '%' . $request->q . '%');
        }
        if ($request->filled('type_lycee')) {
            $query->where('type_lycee', $request->type_lycee);
        }

        $subjects = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.subjects.index', ['subjects' => $subjects, 'types' => self::TYPES]);
    }

    public function create()
    {
        return view('admin.subjects.form', ['subject' => new Subject(), 'types' => self::TYPES]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['name'] = trim($data['name']);
        Subject::create($data);

        return redirect()->route('subjects.index')->with('success', 'Matière créée.');
    }

    public function edit(Subject $subject)
    {
        return view('admin.subjects.form', ['subject' => $subject, 'types' => self::TYPES]);
    }

    public function update(Request $request, Subject $subject)
    {
        $data = $this->validated($request, $subject);
        $data['name'] = trim($data['name']);
        $subject->update($data);

        return redirect()->route('subjects.index')->with('success', 'Matière modifiée.');
    }

    public function destroy(Subject $subject)
    {
        if (
            $subject->courses()->exists() ||
            $subject->summaries()->exists() ||
            $subject->exercises()->exists() ||
            $subject->books()->exists()
        ) {
            return back()->with('error', 'Impossible de supprimer : cette matière possède des contenus associés.');
        }

        $subject->delete();

        return back()->with('success', 'Matière supprimée.');
    }

    private function validated(Request $request, ?Subject $subject = null): array
    {
        return $request->validate([
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('subjects')
                    ->where(fn ($q) => $q->where('type_lycee', $request->type_lycee))
                    ->ignore($subject?->id),
            ],
            'type_lycee' => ['required', Rule::in(array_keys(self::TYPES))],
        ], [
            'name.unique' => 'Cette matière existe déjà pour ce type d’établissement.',
        ]);
    }
}
