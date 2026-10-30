<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Summary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SummaryController extends Controller
{
    public function index(Request $request)
    {
        $query = Summary::with(['teacher.user', 'subject']);

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

        $summaries = $query->latest()->paginate(15)->withQueryString();
        $subjects  = Subject::orderBy('name')->get();

        return view('admin.summaries.index', compact('summaries', 'subjects'));
    }

    public function create()
    {
        return view('admin.summaries.form', [
            'summary'  => new Summary(),
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

        $data['file_path'] = $request->file('file')->store('summaries', 'public');
        unset($data['file']);
        $data['teacher_id'] = null;
        $data['active']     = true;

        Summary::create($data);

        return redirect()->route('summaries.index')->with('success', 'Résumé créé.');
    }

    public function edit(Summary $summary)
    {
        return view('admin.summaries.form', [
            'summary'  => $summary,
            'subjects' => Subject::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Summary $summary)
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
            if ($summary->file_path && Storage::disk('public')->exists($summary->file_path)) {
                Storage::disk('public')->delete($summary->file_path);
            }
            $data['file_path'] = $request->file('file')->store('summaries', 'public');
        }
        unset($data['file']);

        $summary->update($data);

        return redirect()->route('summaries.index')->with('success', 'Résumé modifié.');
    }

    public function toggle(Summary $summary)
    {
        $summary->active = !$summary->active;
        $summary->save();

        return back()->with('success', $summary->active ? 'Résumé activé.' : 'Résumé désactivé.');
    }

    public function download(Summary $summary)
    {
        $path = storage_path('app/public/' . $summary->file_path);
        abort_unless($summary->file_path && file_exists($path), 404, 'Fichier introuvable');

        return response()->download($path);
    }

    public function destroy(Summary $summary)
    {
        if ($summary->file_path && Storage::disk('public')->exists($summary->file_path)) {
            Storage::disk('public')->delete($summary->file_path);
        }
        $summary->delete();

        return back()->with('success', 'Résumé supprimé.');
    }
}
