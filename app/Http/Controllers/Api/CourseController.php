<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    /**
     * Liste des cours
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Course::with(['teacher.user', 'subject']);

        // Filtrage rôle : Seul l'admin peut voir les cours inactifs
        if (!$user || $user->role !== 'admin') {
            $query->where('active', true);
        }

        // Restriction : Seuls les élèves voient un contenu restreint à leur classe
        if ($user && $user->role === 'student') {
            $student = $user->studentProfile;

            if ($student) {
                $query->where('level', $student->niveau)
                      ->where('class', $student->classe);
            } else {
                return response()->json([
                    'status' => true,
                    'data' => [],
                    'pagination' => null
                ]);
            }
        } else {
            // Pour l'admin et les enseignants, le filtrage par niveau/classe est optionnel
            if ($request->level) {
                $query->where('level', $request->level);
            }

            if ($request->class) {
                $query->where('class', $request->class);
            }
        }

        // Pagination
        $courses = $query->paginate(20);

        // Transformation propre (Structure d'objet conservée pour Flutter)
        $formattedCourses = collect($courses->items())->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'level' => $course->level,
                'class' => $course->class,
                'subject' => $course->subject ? [
                    'id' => $course->subject->id,
                    'name' => $course->subject->name
                ] : null,
                'teacher_name' => $course->teacher?->user?->name ?? 'Inconnu',
                'file_path' => $course->file_path,
                'active' => (bool) $course->active,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formattedCourses,
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
                'per_page' => $courses->perPage(),
                'total' => $courses->total(),
            ]
        ]);
    }

    /**
     * Création d’un cours
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'required|string',
            'file'        => 'required|file|max:51200',
            'subject_id'  => 'nullable',
            'subject'     => 'nullable',
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
        $allowedSubjects = [];

        if ($user->role === 'teacher') {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                return response()->json([
                    'status' => false,
                    'message' => 'Profil enseignant introuvable',
                ], 422);
            }
            $teacherId = $teacher->id;
            
            // Extraction des matières depuis le champ 'matieres' de la table teachers
            $rawMatieres = $teacher->matieres ?? [];
            if (is_string($rawMatieres)) {
                $rawMatieres = json_decode($rawMatieres, true) ?? explode(',', $rawMatieres);
            }

            // Nettoyage textuel en minuscules pour comparaison uniforme
            $allowedSubjects = collect($rawMatieres)->map(function($name) {
                return mb_strtolower(trim($name));
            })->toArray();
        }

        // Résolution de la matière reçue depuis l'application
        $subjectId = null;
        $inputSubjectName = '';
        $rawSubject = $request->input('subject_id') ?? $request->input('subject');

        if ($rawSubject) {
            if (is_numeric($rawSubject)) {
                $subject = Subject::find($rawSubject);
                if ($subject) {
                    $subjectId = $subject->id;
                    $inputSubjectName = mb_strtolower(trim($subject->name));
                }
            } else {
                $inputSubjectName = mb_strtolower(trim($rawSubject));
                $subject = Subject::firstOrCreate(['name' => trim($rawSubject)]);
                $subjectId = $subject->id;
            }
        }

        if (!$subjectId) {
            return response()->json([
                'status' => false,
                'message' => 'La matière sélectionnée est requise ou invalide.',
            ], 422);
        }

        // Sécurité : Vérification basée sur la valeur textuelle de la matière
        if ($user->role === 'teacher') {
            if (!in_array($inputSubjectName, $allowedSubjects)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Accès refusé. Vous pouvez uniquement ajouter des contenus pour les matières choisies lors de votre inscription.',
                ], 403);
            }
        }

        $path = $request->file('file')->store('courses', 'public');

        $course = Course::create([
            'teacher_id'  => $teacherId,
            'subject_id'  => $subjectId,
            'title'       => $request->title,
            'description' => $request->description,
            'file_path'   => $path,
            'level'       => $request->level,
            'class'       => $request->class,
            'active'      => false, // Attente de validation admin
        ]);

        $course->load('subject');

        return response()->json([
            'status'  => true,
            'message' => 'Cours créé et en attente de validation admin',
            'course'  => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'level' => $course->level,
                'class' => $course->class,
                'subject' => $course->subject ? [
                    'id' => $course->subject->id,
                    'name' => $course->subject->name
                ] : null,
                'file_path' => $course->file_path,
                'active' => (bool) $course->active,
            ],
        ], 201);
    }

    /**
     * Mise à jour d’un cours
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::findOrFail($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        if ($user->role !== 'admin' && ($user->role === 'teacher' && $course->teacher?->user_id !== $user->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Accès non autorisé pour modifier ce cours',
            ], 403);
        }

        $rawSubject = $request->input('subject_id') ?? $request->input('subject');
        $targetSubjectId = $course->subject_id;
        $inputSubjectName = '';

        if ($rawSubject) {
            if (is_numeric($rawSubject)) {
                $subject = Subject::find($rawSubject);
                if ($subject) {
                    $targetSubjectId = $subject->id;
                    $inputSubjectName = mb_strtolower(trim($subject->name));
                }
            } else {
                $inputSubjectName = mb_strtolower(trim($rawSubject));
                $subject = Subject::firstOrCreate(['name' => trim($rawSubject)]);
                $targetSubjectId = $subject->id;
            }
        }

        // Sécurité lors du changement de matière par l'enseignant
        if ($user->role === 'teacher' && $targetSubjectId != $course->subject_id) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                return response()->json([
                    'status' => false,
                    'message' => 'Profil enseignant introuvable',
                ], 422);
            }
            
            $rawMatieres = $teacher->matieres ?? [];
            if (is_string($rawMatieres)) {
                $rawMatieres = json_decode($rawMatieres, true) ?? explode(',', $rawMatieres);
            }

            $allowedSubjects = collect($rawMatieres)->map(function($name) {
                return mb_strtolower(trim($name));
            })->toArray();

            if (!in_array($inputSubjectName, $allowedSubjects)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Accès refusé. Vous ne pouvez pas attribuer ce cours à une matière qui n\'est pas parmi vos choix.',
                ], 403);
            }
        }

        $course->title = $request->input('title', $course->title);
        $course->description = $request->input('description', $course->description);
        $course->level = $request->input('level', $course->level);
        $course->subject_id = $targetSubjectId;
        
        if ($request->has('class')) {
            $course->class = $request->input('class');
        } elseif ($request->has('classe')) {
            $course->class = $request->input('classe');
        }

        if ($request->hasFile('file')) {
            if ($course->file_path && Storage::disk('public')->exists($course->file_path)) {
                Storage::disk('public')->delete($course->file_path);
            }
            $path = $request->file('file')->store('courses', 'public');
            $course->file_path = $path;
        }

        $course->save();
        $course->load('subject');

        return response()->json([
            "status" => true,
            "message" => "Cours modifié avec succès",
            "course" => [
                'id' => $course->id,
                'title' => $course->title,
                'description' => $course->description,
                'level' => $course->level,
                'class' => $course->class,
                'subject' => $course->subject ? [
                    'id' => $course->subject->id,
                    'name' => $course->subject->name
                ] : null,
                'file_path' => $course->file_path,
                'active' => (bool) $course->active,
            ]
        ]);
    }

    /**
     * Télécharger cours
     */
    public function download(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::findOrFail($id);

        if (!$course->active) {
            return response()->json([
                'message' => 'Cours non validé'
            ], 403);
        }

        if ($user && $user->role === 'student') {
            $student = $user->studentProfile;

            if ($student && ($course->level !== $student->niveau || $course->class !== $student->classe)) {
                return response()->json([
                    'message' => 'Accès interdit'
                ], 403);
            }
        }

        $filePath = storage_path('app/public/' . $course->file_path);
        return response()->download($filePath);
    }

    /**
     * Activer / Désactiver
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
            'message' => $course->active ? 'Cours activé' : 'Cours désactivé',
            'course' => [
                'id' => $course->id,
                'active' => (bool) $course->active
            ]
        ]);
    }

    /**
     * Suppression
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

        if ($course->file_path && Storage::disk('public')->exists($course->file_path)) {
            Storage::disk('public')->delete($course->file_path);
        }

        $course->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cours supprimé avec succès',
        ]);
    }
}