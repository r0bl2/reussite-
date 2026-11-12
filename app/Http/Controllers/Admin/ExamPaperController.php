<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamPaper;
use App\Models\Subject; // <--- 1. Importez le modèle Subject ici
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExamPaperController extends Controller
{
    private const LEVELS = ['6ème', '5ème', '4ème', '3ème', 'Seconde', 'Première', 'Terminale'];

    public function index(Request $request)
    {
        $query = ExamPaper::with('author');
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }
        $papers = $query->latest()->paginate(10);
        $papers->appends($request->query());

        return view('admin.exam-papers.index', compact('papers'));
    }

    public function show(ExamPaper $examPaper)
    {
        $examPaper->load('author');

        return view('admin.exam-papers.show', ['paper' => $examPaper]);
    }

    public function create()
    {
        // 2. Récupérez toutes les matières triées par nom
        $subjects = Subject::orderBy('name')->get();

        return view('admin.exam-papers.form', [
            'levels'   => self::LEVELS,
            'subjects' => $subjects // <--- Transmettez-les à la vue
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'subject'     => 'required|string|exists:subjects,name', // <--- 3. Vérifie que la matière existe dans la table subjects
            'level'       => 'required|in:' . implode(',', self::LEVELS),
            'file'        => 'required|file|max:51200',
        ]);

        if ($request->hasFile('file')) {
            $data['file_path'] = $request->file('file')->store('exam-papers', 'public');
        }

        $data['created_by']  = $request->user()->id;
        $data['status']      = 'approved';
        $data['reviewed_by'] = $request->user()->id;
        $data['reviewed_at'] = now();

        ExamPaper::create($data);

        return redirect()->route('exam-papers.index')->with('success', 'Épreuve ajoutée.');
    }

    public function approve(Request $request, ExamPaper $examPaper)
    {
        $examPaper->update([
            'status' => 'approved', 'rejection_reason' => null,
            'reviewed_by' => $request->user()->id, 'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Épreuve validée.');
    }

    public function reject(Request $request, ExamPaper $examPaper)
    {
        $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $examPaper->update([
            'status' => 'rejected', 'rejection_reason' => $request->rejection_reason,
            'reviewed_by' => $request->user()->id, 'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Épreuve rejetée.');
    }

    public function preview(ExamPaper $examPaper)
    {
        $path = $examPaper->file_path ? storage_path('app/public/' . $examPaper->file_path) : null;
        abort_unless($path && file_exists($path), 404, 'Fichier introuvable');

        return response()->file($path);
    }

    public function download(ExamPaper $examPaper)
    {
        $path = $examPaper->file_path ? storage_path('app/public/' . $examPaper->file_path) : null;
        abort_unless($path && file_exists($path), 404, 'Fichier introuvable');

        return response()->download($path);
    }

    public function destroy(ExamPaper $examPaper)
    {
        if ($examPaper->file_path && Storage::disk('public')->exists($examPaper->file_path)) {
            Storage::disk('public')->delete($examPaper->file_path);
        }
        $examPaper->delete();

        return back()->with('success', 'Épreuve supprimée.');
    }
}
