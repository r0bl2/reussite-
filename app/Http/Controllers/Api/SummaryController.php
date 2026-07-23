<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Summary;
use App\Models\Teacher;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;

class SummaryController extends Controller
{
    /**
     * Liste des résumés
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Summary::with(['teacher.user', 'subject']);

        // Filtrage rôle : Seul l'admin peut voir les résumés inactifs
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
        $summaries = $query->paginate(20);

        // Transformation propre (Structure d'objet conservée pour Flutter)
        $formattedSummaries = collect($summaries->items())->map(function ($summary) {
            return [
                'id' => $summary->id,
                'title' => $summary->title,
                'description' => $summary->description,
                'level' => $summary->level,
                'class' => $summary->class,
                'subject' => $summary->subject ? [
                    'id' => $summary->subject->id,
                    'name' => $summary->subject->name
                ] : null,
                'teacher_name' => $summary->teacher?->user?->name ?? 'Inconnu',
                'file_path' => $summary->file_path,
                'active' => (bool) $summary->active,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formattedSummaries,
            'pagination' => [
                'current_page' => $summaries->currentPage(),
                'last_page' => $summaries->lastPage(),
                'per_page' => $summaries->perPage(),
                'total' => $summaries->total(),
            ]
        ]);
    }

    /**
     * Création d’un résumé
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

        // Stockage dans le dossier 'summaries'
        $path = $request->file('file')->store('summaries', 'public');

        $summary = Summary::create([
            'teacher_id'  => $teacherId,
            'subject_id'  => $subjectId,
            'title'       => $request->title,
            'description' => $request->description,
            'file_path'   => $path,
            'level'       => $request->level,
            'class'       => $request->class,
            'active'      => false, // Attente de validation admin
        ]);

        $summary->load('subject');

        return response()->json([
            'status'  => true,
            'message' => 'Résumé créé et en attente de validation admin',
            'summary' => [
                'id' => $summary->id,
                'title' => $summary->title,
                'description' => $summary->description,
                'level' => $summary->level,
                'class' => $summary->class,
                'subject' => $summary->subject ? [
                    'id' => $summary->subject->id,
                    'name' => $summary->subject->name
                ] : null,
                'file_path' => $summary->file_path,
                'active' => (bool) $summary->active,
            ],
        ], 201);
    }

    /**
     * Mise à jour d’un résumé
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $summary = Summary::findOrFail($id);

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        if ($user->role !== 'admin' && ($user->role === 'teacher' && $summary->teacher?->user_id !== $user->id)) {
            return response()->json([
                'status' => false,
                'message' => 'Accès non autorisé pour modifier ce résumé',
            ], 403);
        }

        $rawSubject = $request->input('subject_id') ?? $request->input('subject');
        $targetSubjectId = $summary->subject_id;
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
        if ($user->role === 'teacher' && $targetSubjectId != $summary->subject_id) {
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
                    'message' => 'Accès refusé. Vous ne pouvez pas attribuer ce résumé à une matière qui n\'est pas parmi vos choix.',
                ], 403);
            }
        }

        $summary->title = $request->input('title', $summary->title);
        $summary->description = $request->input('description', $summary->description);
        $summary->level = $request->input('level', $summary->level);
        $summary->subject_id = $targetSubjectId;
        
        if ($request->has('class')) {
            $summary->class = $request->input('class');
        } elseif ($request->has('classe')) {
            $summary->class = $request->input('classe');
        }

        if ($request->hasFile('file')) {
            if ($summary->file_path && Storage::disk('public')->exists($summary->file_path)) {
                Storage::disk('public')->delete($summary->file_path);
            }
            $path = $request->file('file')->store('summaries', 'public');
            $summary->file_path = $path;
        }

        $summary->save();
        $summary->load('subject');

        return response()->json([
            "status" => true,
            "message" => "Résumé modifié avec succès",
            "summary" => [
                'id' => $summary->id,
                'title' => $summary->title,
                'description' => $summary->description,
                'level' => $summary->level,
                'class' => $summary->class,
                'subject' => $summary->subject ? [
                    'id' => $summary->subject->id,
                    'name' => $summary->subject->name
                ] : null,
                'file_path' => $summary->file_path,
                'active' => (bool) $summary->active,
            ]
        ]);
    }

    /**
     * Télécharger résumé
     */
    /**
     * Télécharger résumé
     */
    public function download(Request $request, $id)
    {
        $user = $request->user();
        $summary = Summary::findOrFail($id);

        // ✅ AUTORISATION : L'admin et l'enseignant créateur peuvent accéder au fichier inactif
        $isAdmin = $user && $user->role === 'admin';
        $isOwnerTeacher = $user && $user->role === 'teacher' && $summary->teacher?->user_id === $user->id;

        if (!$summary->active && !$isAdmin && !$isOwnerTeacher) {
            return response()->json([
                'message' => 'Résumé non validé'
            ], 403);
        }

        // Restriction supplémentaire pour les étudiants
        if ($user && $user->role === 'student') {
            $student = $user->studentProfile;

            if ($student && ($summary->level !== $student->niveau || $summary->class !== $student->classe)) {
                return response()->json([
                    'message' => 'Accès interdit'
                ], 403);
            }
        }

        $filePath = storage_path('app/public/' . $summary->file_path);

        if (!file_exists($filePath)) {
            return response()->json([
                'message' => 'Fichier introuvable sur le serveur'
            ], 404);
        }

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

        $summary = Summary::findOrFail($id);
        $summary->active = !$summary->active;
        $summary->save();

        return response()->json([
            'status' => true,
            'message' => $summary->active ? 'Résumé activé' : 'Résumé désactivé',
            'summary' => [
                'id' => $summary->id,
                'active' => (bool) $summary->active
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

        $summary = Summary::findOrFail($id);

        if ($summary->file_path && Storage::disk('public')->exists($summary->file_path)) {
            Storage::disk('public')->delete($summary->file_path);
        }

        $summary->delete();

        return response()->json([
            'status' => true,
            'message' => 'Résumé supprimé avec succès',
        ]);
    }
}