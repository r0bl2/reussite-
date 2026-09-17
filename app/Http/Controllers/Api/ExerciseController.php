<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exercise;
use App\Models\Subject;
use Illuminate\Support\Facades\Storage;

class ExerciseController extends Controller
{
    /**
     * Mêmes règles de validation fichier que CourseController.
     * (Ajout de jpg/png car l'ancien ExerciseController les acceptait déjà.)
     */
    private const FILE_VALIDATION_RULE = 'required|file|mimes:pdf,doc,docx,jpg,png|max:51200';
    private const FILE_VALIDATION_RULE_OPTIONAL = 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:51200';
    private const CORRECTION_VALIDATION_RULE_OPTIONAL = 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:51200';

    /**
     * Helper de normalisation texte : minuscule + trim + suppression accents.
     * Reproduit exactement le $cleanStr() de CourseController::download()
     * pour que la comparaison niveau/classe soit tolérante aux accents/casse.
     */
    private function cleanStr($str): string
    {
        if (!$str) return '';
        $str = mb_strtolower(trim((string) $str));
        $str = str_replace(
            ['è', 'é', 'ê', 'ë', 'à', 'â', 'ô'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'o'],
            $str
        );
        return $str;
    }

    /**
     * Helper pour formater la réponse d'un exercice (garde les noms de champs
     * déjà consommés par Flutter : titre, matiere, exercise_path, ...).
     */
    private function formatExerciseData(Exercise $exercise): array
    {
        return [
            'id'              => $exercise->id,
            'titre'           => $exercise->title,
            'matiere'         => $exercise->subject ? $exercise->subject->name : ($exercise->subject_id ?? null),
            'niveau'          => $exercise->niveau,
            'classe'          => $exercise->classe,
            'exercise_path'   => $exercise->file_path ? asset('storage/' . $exercise->file_path) : null,
            'correction_path' => $exercise->correction_path ? asset('storage/' . $exercise->correction_path) : null,
            'is_active'       => (bool) $exercise->active,
            'created_at'      => $exercise->created_at ? $exercise->created_at->format('d/m/Y') : now()->format('d/m/Y'),
        ];
    }

    /**
     * ==========================================
     * LISTE DES EXERCICES (même logique de rôle que CourseController::index)
     * ==========================================
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $userRole = $user ? mb_strtolower(trim($user->role)) : null;

        $query = Exercise::with(['teacher', 'subject'])->orderBy('created_at', 'desc');

        // 1. FILTRAGE PAR RÔLE & VISIBILITÉ
        if ($userRole === 'admin') {
            // L'admin voit TOUT (actif + inactif)
        } elseif ($userRole === 'teacher') {
            // L'enseignant voit les exercices ACTIFS + LES SIENS (même inactifs)
            // teacher_id référence directement users.id (voir Exercise::teacher())
            $query->where(function ($q) use ($user) {
                $q->where('active', true)
                  ->orWhere('teacher_id', $user->id);
            });
        } else {
            // Élèves / visiteurs : uniquement les exercices actifs
            $query->where('active', true);
        }

        // 2. RESTRICTIONS ÉLÈVES (niveau/classe tolérant accents + casse)
        if ($userRole === 'student') {
            $student = $user->studentProfile;

            if (!$student) {
                return response()->json([
                    'status' => true,
                    'data'   => [],
                ]);
            }

            $studentNiveau = $this->cleanStr($student->niveau);
            $studentClasse = $this->cleanStr($student->classe);

            $exercises = $query->get()->filter(function ($ex) use ($studentNiveau, $studentClasse) {
                return $this->cleanStr($ex->niveau) === $studentNiveau
                    && $this->cleanStr($ex->classe) === $studentClasse;
            })->values();

            return response()->json([
                'status' => true,
                'data'   => $exercises->map(fn ($ex) => $this->formatExerciseData($ex)),
            ]);
        }

        // 3. Filtres manuels admin/teacher
        if ($request->filled('niveau')) {
            $query->where('niveau', $request->niveau);
        }
        if ($request->filled('classe')) {
            $query->where('classe', $request->classe);
        }

        $exercises = $query->get();

        return response()->json([
            'status' => true,
            'data'   => $exercises->map(fn ($ex) => $this->formatExerciseData($ex)),
        ]);
    }

    /**
     * ==========================================
     * CRÉATION D'UN EXERCICE
     * ==========================================
     */
    public function store(Request $request)
    {
        $request->validate([
            'titre'           => 'required|string|max:255',
            'matiere'         => 'required',
            'niveau'          => 'required|string',
            'classe'          => 'required|string',
            'exercise_file'   => self::FILE_VALIDATION_RULE,
            'correction_file' => self::CORRECTION_VALIDATION_RULE_OPTIONAL,
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

        // Exercise::teacher() référence directement users.id (pas la table 'teachers')
        $teacherId = $user->id;

        // Résolution matière (texte ou ID), même logique que CourseController
        $subjectId = null;
        $rawSubject = $request->input('matiere');

        if (is_numeric($rawSubject)) {
            $subject = Subject::find($rawSubject);
            $subjectId = $subject?->id;
        } else {
            $subject = Subject::firstOrCreate(['name' => trim($rawSubject)]);
            $subjectId = $subject->id;
        }

        if (!$subjectId) {
            return response()->json([
                'status'  => false,
                'message' => 'La matière sélectionnée est requise ou invalide.',
            ], 422);
        }

        $exercisePath = $request->file('exercise_file')->store('exercises', 'public');
        $correctionPath = null;

        if ($request->hasFile('correction_file')) {
            $correctionPath = $request->file('correction_file')->store('corrections', 'public');
        }

        // Actif par défaut si Admin, sinon en attente de validation (même règle qu'avant)
        $isActive = ($userRole === 'admin');

        $exercise = Exercise::create([
            'teacher_id'      => $teacherId,
            'title'           => $request->titre,
            'subject_id'      => $subjectId,
            'niveau'          => $request->niveau,
            'classe'          => $request->classe,
            'file_path'       => $exercisePath,
            'correction_path' => $correctionPath,
            'active'          => $isActive,
        ]);

        $exercise->load(['subject', 'teacher']);

        return response()->json([
            'status'  => true,
            'message' => $isActive ? 'Exercice enregistré avec succès' : 'Exercice enregistré et en attente de validation admin',
            'data'    => $this->formatExerciseData($exercise),
        ], 201);
    }

    /**
     * ==========================================
     * MISE À JOUR D'UN EXERCICE
     * ==========================================
     */
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $exercise = Exercise::findOrFail($id);

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'Utilisateur non authentifié',
            ], 401);
        }

        $userRole = mb_strtolower(trim($user->role));

        if ($userRole !== 'admin' && ($userRole === 'teacher' && $exercise->teacher_id !== $user->id)) {
            return response()->json([
                'status'  => false,
                'message' => 'Accès non autorisé pour modifier cet exercice',
            ], 403);
        }

        $request->validate([
            'titre'           => 'sometimes|string|max:255',
            'niveau'          => 'nullable|string',
            'classe'          => 'nullable|string',
            'exercise_file'   => self::FILE_VALIDATION_RULE_OPTIONAL,
            'correction_file' => self::CORRECTION_VALIDATION_RULE_OPTIONAL,
        ]);

        $exercise->title  = $request->input('titre', $exercise->title);
        $exercise->niveau = $request->input('niveau', $exercise->niveau);
        $exercise->classe = $request->input('classe', $exercise->classe);

        if ($request->hasFile('exercise_file')) {
            if ($exercise->file_path && Storage::disk('public')->exists($exercise->file_path)) {
                Storage::disk('public')->delete($exercise->file_path);
            }
            $exercise->file_path = $request->file('exercise_file')->store('exercises', 'public');
        }

        if ($request->hasFile('correction_file')) {
            if ($exercise->correction_path && Storage::disk('public')->exists($exercise->correction_path)) {
                Storage::disk('public')->delete($exercise->correction_path);
            }
            $exercise->correction_path = $request->file('correction_file')->store('corrections', 'public');
        }

        $exercise->save();
        $exercise->load(['subject', 'teacher']);

        return response()->json([
            'status'  => true,
            'message' => 'Exercice modifié avec succès',
            'data'    => $this->formatExerciseData($exercise),
        ]);
    }

    /**
     * ==========================================
     * ACTIVER / DÉSACTIVER (LA MÉTHODE QUI MANQUAIT)
     * ==========================================
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

        $exercise = Exercise::findOrFail($id);
        $exercise->active = !$exercise->active;
        $exercise->save();

        return response()->json([
            'status'  => true,
            'message' => $exercise->active ? 'Exercice activé' : 'Exercice désactivé',
            'data'    => [
                'id'     => $exercise->id,
                'active' => (bool) $exercise->active,
            ],
        ]);
    }

    /**
     * ==========================================
     * TÉLÉCHARGER L'EXERCICE (sécurisé, même logique que CourseController::download)
     * ==========================================
     */
    public function download(Request $request, $id, $type = 'exercise')
    {
        $user = $request->user();
        $exercise = Exercise::findOrFail($id);

        $userRole = $user ? mb_strtolower(trim($user->role)) : null;
        $isAdmin = $userRole === 'admin';
        $isOwnerTeacher = $userRole === 'teacher' && $exercise->teacher_id === $user?->id;

        if (!$exercise->active && !$isAdmin && !$isOwnerTeacher) {
            return response()->json([
                'message' => "Exercice non validé par l'administration",
            ], 403);
        }

        if ($userRole === 'student') {
            $student = $user->studentProfile;

            $studentNiveau = $this->cleanStr($student->niveau ?? '');
            $studentClasse = $this->cleanStr($student->classe ?? '');
            $exerciseNiveau = $this->cleanStr($exercise->niveau ?? '');
            $exerciseClasse = $this->cleanStr($exercise->classe ?? '');

            if ($exerciseNiveau !== $studentNiveau || $exerciseClasse !== $studentClasse) {
                return response()->json([
                    'message' => 'Accès interdit : niveau/classe non correspondant.',
                ], 403);
            }
        }

        // {type?} attendu par la route : "exercise" (défaut) ou "correction"
        $type = mb_strtolower(trim((string) $type)) ?: 'exercise';

        if ($type === 'correction') {
            if (!$exercise->correction_path) {
                return response()->json([
                    'message' => 'Aucune correction disponible pour cet exercice',
                ], 404);
            }
            $relativePath = $exercise->correction_path;
        } else {
            $relativePath = $exercise->file_path;
        }

        $filePath = storage_path('app/public/' . $relativePath);

        if (!file_exists($filePath)) {
            return response()->json([
                'message' => 'Fichier introuvable sur le serveur',
            ], 404);
        }

        return response()->download($filePath);
    }

    /**
     * ==========================================
     * SUPPRESSION D'UN EXERCICE ET DE SES FICHIERS
     * ==========================================
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

        $exercise = Exercise::findOrFail($id);

        if ($exercise->file_path) {
            Storage::disk('public')->delete($exercise->file_path);
        }
        if ($exercise->correction_path) {
            Storage::disk('public')->delete($exercise->correction_path);
        }

        $exercise->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Exercice supprimé avec succès',
        ]);
    }
}