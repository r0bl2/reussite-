<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeTutorDemand;
use Illuminate\Http\Request;

class HomeTutorDemandController extends Controller
{
    public function index(Request $request)
    {
        $query = HomeTutorDemand::where('status', 'pending');
        if ($request->filled('q')) {
            $query->where(fn ($q) => $q->where('last_name', 'like', '%' . $request->q . '%')->orWhere('first_name', 'like', '%' . $request->q . '%'));
        }
        $demands = $query->latest()->paginate(15);
        $demands->appends($request->query());

        return view('admin.home-tutor-demands.index', compact('demands'));
    }

    public function history(Request $request)
    {
        $query = HomeTutorDemand::whereIn('status', ['approved', 'rejected']);
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $demands = $query->latest('updated_at')->paginate(15);
        $demands->appends($request->query());

        return view('admin.home-tutor-demands.history', compact('demands'));
    }

    public function show(HomeTutorDemand $homeTutorDemand)
    {
        return view('admin.home-tutor-demands.show', ['demand' => $homeTutorDemand]);
    }

    public function approve(Request $request, HomeTutorDemand $homeTutorDemand)
    {
        $homeTutorDemand->update(['status' => 'approved', 'admin_comment' => $request->input('admin_comment')]);

        return redirect()->route('home-tutor-demands.index')->with('success', 'Demande validée.');
    }

    public function reject(Request $request, HomeTutorDemand $homeTutorDemand)
    {
        $request->validate(['admin_comment' => 'required|string|max:1000']);
        $homeTutorDemand->update(['status' => 'rejected', 'admin_comment' => $request->admin_comment]);

        return redirect()->route('home-tutor-demands.index')->with('success', 'Demande rejetée.');
    }

    public function file(HomeTutorDemand $homeTutorDemand, string $field)
    {
        abort_unless(in_array($field, ['photo_path', 'diploma_path', 'cv_path'], true), 404);
        $rel = $homeTutorDemand->$field;
        $path = $rel ? storage_path('app/public/' . $rel) : null;
        abort_unless($path && file_exists($path), 404, 'Fichier introuvable');

        return response()->file($path);
    }
}
