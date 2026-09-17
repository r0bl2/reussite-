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
     * Règle de validation du fichier de cours, réutilisée par store() et update().
     * On limite volontairement aux formats consultables/téléchargeables comme "cours" :
     * PDF (lecture via SfPdfViewer côté app) + quelques formats bureautiques courants.
     * Ajuste la liste `mimes` si tu veux n'accepter QUE le PDF.
     */
    private const FILE_VALIDATION_RULE = 'required|file|mimes:pdf,doc,docx,ppt,pptx|max:51200';
    private const FILE_VALIDATION_RULE_OPTIONAL = 'nullable|file|mimes:pdf,doc,docx,ppt,pptx|max:51200';

    /**
     * Helper pour formater la réponse d'un cours avec les données enseignant et matière
     */
    private function formatCourseData(Course $course): array
    {
        $teacher = $course->teacher;
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
            // --- CAS ADMIN (On remplit avec les infos du cours) ---
            $niveaux = [];
            if ($course->level) {
                $niveaux[] = $course->class ? "{$course->level} ({$course->class})" : $course->level;
            } else {
                $niveaux[] = "Tous niveaux";
            }

            $matieres = [];
            if ($course->subject) {
                $matieres[] = $course->subject->name;
            } else {
                $matieres[] = "Général";
            }

            $typeLycee = "Établissement Général";
        }

        return [
            'id'           => $course->id,
            'title'        => $course->title,
            'description'  => $course->description,
            'level'        => $course->level ?? 'Non spécifié',
            'class'        => $course->class ?? 'Toutes',
            'subject'      => $course->subject ? [
                'id'   => $course->subject->id,
                'name' => $course->subject->name,
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

            'file_path'   => $course->file_path,
            'active'      => (bool) $course->active,
        ];
    }

    /**
     * Liste des cours avec filtrage adapté selon le rôle
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userRole = $user ? mb_strtolower(trim($user->role)) : null;

        $query = Course::with(['teacher.user', 'subject']);

        // 1. FILTRAGE PAR RÔLE & VISIBILITÉ DES COURS INACTIFS
        if ($userRole === 'admin') {
            // L'admin voit TOUS les cours (actifs et inactifs)
        } elseif ($userRole === 'teacher') {
            // L'enseignant voit les cours ACTIFS + SES PROPRES COURS (même inactifs)
            $teacher = Teacher::where('user_id', $user->id)->first();
            $teacherId = $teacher ? $teacher->id : null;

            $query->where(function ($q) use ($teacherId) {
                $q->where('active', true);
                if ($teacherId) {
                    $q->orWhere('teacher_id', $teacherId);
                }
            });
        } else {
            // Les élèves et visiteurs ne voient QUE les cours actifs
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
        $courses = $query->latest()->paginate(20);

        $formattedCourses = collect($courses->items())->map(function ($course) {
            return $this->formatCourseData($course);
        });

        return response()->json([
            'status'     => true,
            'data'       => $formattedCourses,
            'pagination' => [
                'current_page' => $courses->currentPage(),
                'last_page'    => $courses->lastPage(),
                'per_page'     => $courses->perPage(),
                'total'        => $courses->total(),
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
            // 🔥 FIX : on n'acceptait que "file|max:51200" -> n'importe quel type de fichier
            // (json, image, exe...) était accepté comme "cours". On restreint désormais
            // aux formats réellement consultables par l'app (PDF en priorité).
            'file'        => self::FILE_VALIDATION_RULE,
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

        $path = $request->file('file')->store('courses', 'public');

        $course = Course::create([
            'teacher_id'  => $teacherId,
            'subject_id'  => $subjectId,
            'title'       => $request->title,
            'description' => $request->description,
            'file_path'   => $path,
            'level'       => $request->level,
            'class'       => $request->class ?? $request->classe,
            'active'      => false, // Attente de validation admin
        ]);

        $course->load(['subject', 'teacher.user']);

        return response()->json([
            'status'  => true,
            'message' => 'Cours créé et en attente de validation admin',
            'course'  => $this->formatCourseData($course),
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
                'status'  => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $userRole = mb_strtolower(trim($user->role));

        if ($userRole !== 'admin' && ($userRole === 'teacher' && $course->teacher?->user_id !== $user->id)) {
            return response()->json([
                'status'  => false,
                'message' => 'Accès non autorisé pour modifier ce cours',
            ], 403);
        }

        // 🔥 FIX : update() n'appelait aucune validation auparavant.
        // On valide maintenant les champs texte (optionnels ici car update partiel)
        // et surtout le fichier, s'il est fourni, avec la même règle mimes que store().
        $request->validate([
            'title'       => 'sometimes|string',
            'description' => 'sometimes|string',
            'level'       => 'nullable|string',
            'class'       => 'nullable|string',
            'classe'      => 'nullable|string',
            'file'        => self::FILE_VALIDATION_RULE_OPTIONAL,
        ]);

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
        if ($userRole === 'teacher' && $targetSubjectId != $course->subject_id) {
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
        $course->load(['subject', 'teacher.user']);

        return response()->json([
            'status'  => true,
            'message' => 'Cours modifié avec succès',
            'course'  => $this->formatCourseData($course),
        ]);
    }

    /**
     * Télécharger cours
     */
    public function download(Request $request, $id)
    {
        $user = $request->user();
        $course = Course::findOrFail($id);

        $userRole = $user ? mb_strtolower(trim($user->role)) : null;

        $isAdmin = $userRole === 'admin';
        $isOwnerTeacher = $userRole === 'teacher' && $course->teacher?->user_id === $user?->id;

        // 1. Vérification si le cours est actif
        if (!$course->active && !$isAdmin && !$isOwnerTeacher) {
            return response()->json([
                'message' => 'Cours non validé par l\'administration'
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

            $courseLevel = $cleanStr($course->level ?? '');
            $courseClass = $cleanStr($course->class ?? '');

            // Tolerances de correspondance
            $levelMatches = ($courseLevel === $userLevel)
                || empty($courseLevel)
                || $courseLevel === 'tous niveaux'
                || $courseLevel === 'tous';

            $classMatches = ($courseClass === $userClass)
                || empty($courseClass)
                || $courseClass === 'toutes'
                || $courseClass === 'toutes les classes';

            if (!$levelMatches || !$classMatches) {
                return response()->json([
                    'message' => "Accès interdit : Niveau/Classe non correspondant. (Cours: '{$course->level}' / '{$course->class}' vs Élève: '{$rawUserLevel}' / '{$rawUserClass}')"
                ], 403);
            }
        }

        $filePath = storage_path('app/public/' . $course->file_path);

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

        $course = Course::findOrFail($id);
        $course->active = !$course->active;
        $course->save();

        return response()->json([
            'status'  => true,
            'message' => $course->active ? 'Cours activé' : 'Cours désactivé',
            'course'  => [
                'id'     => $course->id,
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

        if (!$user || mb_strtolower(trim($user->role)) !== 'admin') {
            return response()->json([
                'status'  => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $course = Course::findOrFail($id);

        if ($course->file_path && Storage::disk('public')->exists($course->file_path)) {
            Storage::disk('public')->delete($course->file_path);
        }

        $course->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Cours supprimé avec succès',
        ]);
    }
}