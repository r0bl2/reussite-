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
     * Helper pour formater la réponse d'un résumé avec les données enseignant et matière
     */
    private function formatSummaryData(Summary $summary): array
    {
        $teacher = $summary->teacher;
        $teacherUser = $teacher?->user;

        // 1. Identification du Nom et de l'Avatar
        $creatorName = $teacherUser?->username ?? $teacherUser?->name ?? 'Administration';

        $avatarUrl = null;
        if ($teacherUser && !empty($teacherUser->avatar)) {
            $avatarUrl = filter_var($teacherUser->avatar, FILTER_VALIDATE_URL)
                ? $teacherUser->avatar
                : asset('storage/' . $teacherUser->avatar);
        } elseif ($teacher && !empty($teacher->photo)) {
            $avatarUrl = filter_var($teacher->photo, FILTER_VALIDATE_URL)
                ? $teacher->photo
                : asset('storage/' . $teacher->photo);
        }

        // 2. Gestion séparée : Enseignant vs Admin
        if ($teacher) {
            // --- CAS ENSEIGNANT ---
            $niveaux = $teacher->niveaux;
            if (is_string($niveaux)) {
                $niveaux = json_decode($niveaux, true) ?? explode(',', $niveaux);
            }

            $matieres = $teacher->matieres;
            if (is_string($matieres)) {
                $matieres = json_decode($matieres, true) ?? explode(',', $matieres);
            }

            $typeLycee = $teacher->type_lycee ?? $teacher->establishment ?? 'Non renseigné';
        } else {
            // --- CAS ADMIN (On remplit avec les infos du résumé) ---
            $niveaux = [];
            if ($summary->level) {
                $niveaux[] = $summary->class ? "{$summary->level} ({$summary->class})" : $summary->level;
            } else {
                $niveaux[] = "Tous niveaux";
            }

            $matieres = [];
            if ($summary->subject) {
                $matieres[] = $summary->subject->name;
            } else {
                $matieres[] = "Général";
            }

            $typeLycee = "Établissement Général";
        }

        return [
            'id'           => $summary->id,
            'title'        => $summary->title,
            'description'  => $summary->description,
            'level'        => $summary->level ?? 'Non spécifié',
            'class'        => $summary->class ?? 'Toutes',
            'subject'      => $summary->subject ? [
                'id'   => $summary->subject->id,
                'name' => $summary->subject->name,
            ] : null,
            'teacher_name' => $creatorName,

            // OBJET ENSEIGNANT / CRÉATEUR GARANTI SANS NULL POUR FLUTTER
            'teacher'     => [
                'id'         => $teacher?->id,
                'user_id'    => $teacher?->user_id,
                'name'       => $creatorName,
                'avatar_url' => $avatarUrl,
                'type_lycee' => $typeLycee,
                'niveaux'    => !empty($niveaux) ? $niveaux : ['Non renseigné'],
                'matieres'   => !empty($matieres) ? $matieres : ['Général'],
            ],

            'file_path'   => $summary->file_path,
            'active'      => (bool) $summary->active,
        ];
    }

    /**
     * Liste des résumés avec filtrage adapté selon le rôle
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userRole = $user ? mb_strtolower(trim($user->role)) : null;

        $query = Summary::with(['teacher.user', 'subject']);

        // 1. FILTRAGE PAR RÔLE & VISIBILITÉ DES RÉSUMÉS INACTIFS
        if ($userRole === 'admin') {
            // L'admin voit TOUS les résumés (actifs et inactifs)
        } elseif ($userRole === 'teacher') {
            // L'enseignant voit les résumés ACTIFS + SES PROPRES RÉSUMÉS (même inactifs)
            $teacher = Teacher::where('user_id', $user->id)->first();
            $teacherId = $teacher ? $teacher->id : null;

            $query->where(function ($q) use ($teacherId) {
                $q->where('active', true);
                if ($teacherId) {
                    $q->orWhere('teacher_id', $teacherId);
                }
            });
        } else {
            // Les élèves et visiteurs ne voient QUE les résumés actifs
            $query->where('active', true);
        }

        // 2. RESTRICTIONS SPÉCIFIQUES AUX ÉLÈVES ET FILTRES REQUÊTE
        if ($userRole === 'student') {
            $student = $user->studentProfile;

            if ($student) {
                $query->where('level', $student->niveau)
                      ->where('class', $student->classe);
            } else {
                return response()->json([
                    'status'     => true,
                    'data'       => [],
                    'pagination' => null
                ]);
            }
        } else {
            // Filtrage optionnel par niveau et classe pour admins/enseignants
            if ($request->filled('level')) {
                $query->where('level', $request->level);
            }

            if ($request->filled('class')) {
                $query->where('class', $request->class);
            }
        }

        // 3. PAGINATION ET FORMATAGE
        $summaries = $query->latest()->paginate(20);

        $formattedSummaries = collect($summaries->items())->map(function ($summary) {
            return $this->formatSummaryData($summary);
        });

        return response()->json([
            'status'     => true,
            'data'       => $formattedSummaries,
            'pagination' => [
                'current_page' => $summaries->currentPage(),
                'last_page'    => $summaries->lastPage(),
                'per_page'     => $summaries->perPage(),
                'total'        => $summaries->total(),
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
                'status'  => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $userRole = mb_strtolower(trim($user->role));

        if (!in_array($userRole, ['admin', 'teacher'])) {
            return response()->json([
                'status'  => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $teacherId = null;
        $allowedSubjects = [];

        if ($userRole === 'teacher') {
            // Recherche du profil ou création automatique s'il manque
            $teacher = Teacher::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'type_lycee' => $user->type_lycee ?? 'lycee_moderne',
                    'niveaux'    => $request->input('level') ? [$request->input('level')] : [],
                    'matieres'   => $request->input('subject') ? [$request->input('subject')] : [],
                ]
            );

            $teacherId = $teacher->id;

            // Extraction des matières depuis le champ 'matieres'
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
                'status'  => false,
                'message' => 'La matière sélectionnée est requise ou invalide.',
            ], 422);
        }

        // Sécurité : Vérification basée sur la valeur textuelle de la matière pour les enseignants
        if ($userRole === 'teacher' && !empty($allowedSubjects)) {
            if (!in_array($inputSubjectName, $allowedSubjects)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Accès refusé. Vous pouvez uniquement ajouter des contenus pour les matières choisies lors de votre inscription.',
                ], 403);
            }
        }

        $path = $request->file('file')->store('summaries', 'public');

        $summary = Summary::create([
            'teacher_id'  => $teacherId,
            'subject_id'  => $subjectId,
            'title'       => $request->title,
            'description' => $request->description,
            'file_path'   => $path,
            'level'       => $request->level,
            'class'       => $request->class ?? $request->classe,
            'active'      => false, // Attente de validation admin
        ]);

        $summary->load(['subject', 'teacher.user']);

        return response()->json([
            'status'  => true,
            'message' => 'Résumé créé et en attente de validation admin',
            'summary' => $this->formatSummaryData($summary),
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
                'status'  => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $userRole = mb_strtolower(trim($user->role));

        if ($userRole !== 'admin' && ($userRole === 'teacher' && $summary->teacher?->user_id !== $user->id)) {
            return response()->json([
                'status'  => false,
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
        if ($userRole === 'teacher' && $targetSubjectId != $summary->subject_id) {
            $teacher = Teacher::where('user_id', $user->id)->first();
            if (!$teacher) {
                return response()->json([
                    'status'  => false,
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
                    'status'  => false,
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
        $summary->load(['subject', 'teacher.user']);

        return response()->json([
            'status'  => true,
            'message' => 'Résumé modifié avec succès',
            'summary' => $this->formatSummaryData($summary),
        ]);
    }

    /**
     * Télécharger un résumé
     */
    public function download(Request $request, $id)
    {
        $user = $request->user();
        $summary = Summary::findOrFail($id);

        $userRole = $user ? mb_strtolower(trim($user->role)) : null;

        $isAdmin = $userRole === 'admin';
        $isOwnerTeacher = $userRole === 'teacher' && $summary->teacher?->user_id === $user?->id;

        // 1. Vérification si le résumé est actif
        if (!$summary->active && !$isAdmin && !$isOwnerTeacher) {
            return response()->json([
                'message' => 'Résumé non validé par l\'administration'
            ], 403);
        }

        // 2. Vérification pour les élèves
        if ($userRole === 'student') {
            // Charger la relation profil étudiant si elle existe
            $studentProfile = $user->studentProfile ?? $user->student ?? null;

            // Récupération des valeurs depuis $user OU depuis la relation du profil
            $rawUserLevel = $user->niveau ?? $user->level ?? $studentProfile?->niveau ?? $studentProfile?->level ?? '';
            $rawUserClass = $user->classe ?? $user->class ?? $studentProfile?->classe ?? $studentProfile?->class ?? '';

            // Helper pour nettoyer et normaliser les chaînes
            $cleanStr = function ($str) {
                if (!$str) return '';
                $str = mb_strtolower(trim((string)$str));
                $str = str_replace(['è', 'é', 'ê', 'ë', 'à', 'â', 'ô'], ['e', 'e', 'e', 'e', 'a', 'a', 'o'], $str);
                return $str;
            };

            $userLevel  = $cleanStr($rawUserLevel);
            $userClass  = $cleanStr($rawUserClass);

            $summaryLevel = $cleanStr($summary->level ?? '');
            $summaryClass = $cleanStr($summary->class ?? '');

            // Tolérances de correspondance
            $levelMatches = ($summaryLevel === $userLevel) 
                || empty($summaryLevel) 
                || $summaryLevel === 'tous niveaux' 
                || $summaryLevel === 'tous';

            $classMatches = ($summaryClass === $userClass) 
                || empty($summaryClass) 
                || $summaryClass === 'toutes' 
                || $summaryClass === 'toutes les classes';

            if (!$levelMatches || !$classMatches) {
                return response()->json([
                    'message' => "Accès interdit : Niveau/Classe non correspondant. (Résumé: '{$summary->level}' / '{$summary->class}' vs Élève: '{$rawUserLevel}' / '{$rawUserClass}')"
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

        if (!$user || mb_strtolower(trim($user->role)) !== 'admin') {
            return response()->json([
                'status'  => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $summary = Summary::findOrFail($id);
        $summary->active = !$summary->active;
        $summary->save();

        return response()->json([
            'status'  => true,
            'message' => $summary->active ? 'Résumé activé' : 'Résumé désactivé',
            'summary' => [
                'id'     => $summary->id,
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

        if (!$user || mb_strtolower(trim($user->role)) !== 'admin') {
            return response()->json([
                'status'  => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $summary = Summary::findOrFail($id);

        if ($summary->file_path && Storage::disk('public')->exists($summary->file_path)) {
            Storage::disk('public')->delete($summary->file_path);
        }

        $summary->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Résumé supprimé avec succès',
        ]);
    }
}