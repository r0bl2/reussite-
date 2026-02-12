<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
{
    // Liste des étudiants (admin uniquement)
    public function index()
    {
        return response()->json(Student::with('user')->get());
    }

    // Mise à jour du profil étudiant
    public function update(Request $request)
    {
        $student = $request->user()->studentProfile;

        if (!$student) {
            return response()->json(['message' => 'Profil étudiant introuvable'], 404);
        }

        $student->update($request->only(['school_type', 'level', 'class']));

        return response()->json([
            'status' => true,
            'message' => 'Profil mis à jour',
            'data' => $student
        ]);
    }
}
