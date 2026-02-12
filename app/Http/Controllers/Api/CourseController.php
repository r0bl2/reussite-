<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;

class CourseController extends Controller
{
    /**
     *  Liste des cours
     * - Admin : tous les cours
     * - Autres : uniquement les cours actifs
     */
    public function index(Request $request)
{
    $user = $request->user();

    $query = Course::query();

    // 🔒 Non admin → seulement les cours actifs
    if (!$user || $user->role !== 'admin') {
        $query->where('active', true);
    }

    // 🎓 Élève → filtrage strict
    if ($user && $user->role === 'student') {
        $student = $user->studentProfile;

        if ($student) {
            $query->where('level', $student->level)
                  ->where('class', $student->class);
        }
    }

    // 🔍 Filtres optionnels (enseignant / parent)
    if ($request->level) {
        $query->where('level', $request->level);
    }

    if ($request->class) {
        $query->where('class', $request->class);
    }

    return response()->json(
        $query->with(['teacher.user', 'subject'])->paginate(20)
    );
}

    /**
     *  Création d’un cours (ADMIN & TEACHER)
     *  TOUS les cours sont créés INACTIFS
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'required|string',
            'file'        => 'required|file|max:10240',
            'subject_id'  => 'nullable|exists:subjects,id',
            'level'       => 'nullable|string',
            'class'       => 'nullable|string',
        ]);

        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        if (!in_array($user->role, ['admin', 'teacher'])) {
            return response()->json([
                'status' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $teacherId = null;

        // Si enseignant → rattacher au teacher
        if ($user->role === 'teacher') {
            $teacher = $user->teacherProfile;

            if (!$teacher) {
                return response()->json([
                    'status' => false,
                    'message' => 'Profil enseignant introuvable',
                ], 422);
            }

            $teacherId = $teacher->id;
        }

        // Stockage du fichier
        $path = $request->file('file')->store('courses', 'public');

        // Création du cours
        $course = Course::create([
            'teacher_id' => $teacherId, // null si admin
            'subject_id' => $request->subject_id,
            'title'      => $request->title,
            'description'=> $request->description,
            'file_path'  => $path,
            'level'      => $request->level,
            'class'      => $request->class,
            'active'     => false, 
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Cours créé et en attente de validation admin',
            'course'  => $course,
        ], 201);
    }

    /**
     * 📌 Téléchargement (uniquement si validé)
     */
    public function download($id)
    {
        $course = Course::findOrFail($id);

        if (!$course->active) {
            return response()->json([
                'status' => false,
                'message' => 'Cours non encore validé',
            ], 403);
        }

        return response()->download(
            storage_path('app/public/' . $course->file_path)
        );
    }

    /**
     * 📌 Activation / désactivation d’un cours (Admin uniquement)
     */
    public function toggleActive(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || $user->role != 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $course = Course::findOrFail($id);
        $course->active = !$course->active;
        $course->save();

        return response()->json([
            'status' => true,
            'message' => 'Cours mis à jour',
            'course' => $course,
        ]);
    }

    /**
     * 📌 Suppression d’un cours (Admin uniquement)
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (!$user || $user->role != 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $course = Course::findOrFail($id);
        $course->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cours supprimé',
        ]);
    }
}
