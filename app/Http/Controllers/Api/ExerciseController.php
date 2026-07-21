<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Exercise;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Exception;

class ExerciseController extends Controller
{
    /**
     * ==========================================
     * LISTE DES EXERCICES (AVEC FILTRES ET API)
     * ==========================================
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Chargement de la relation pour récupérer proprement le nom de la matière
            $query = Exercise::with('subject')->orderBy('created_at', 'desc');

            /**
             * Filtrage Étudiant & Visibilité
             */
            if ($user && $user->role === 'student') {
                // Les élèves ne voient que les exercices actifs
                $query->where('active', 1);

                $student = $user->studentProfile;

                if ($student) {
                    $query->where('niveau', $student->niveau)
                          ->where('classe', $student->classe);
                } else {
                    return response()->json([
                        'status' => true,
                        'data'   => []
                    ]);
                }
            }

            /**
             * Filtrage Manuel (Admin / Enseignants)
             */
            if ($request->filled('niveau')) {
                $query->where('niveau', $request->niveau);
            }

            if ($request->filled('classe')) {
                $query->where('classe', $request->classe);
            }

            $exercises = $query->get();

            /**
             * Formatage de la réponse API pour Flutter
             */
            $data = $exercises->map(function ($ex) {
                return [
                    'id'              => $ex->id,
                    'titre'           => $ex->title,
                    'matiere'         => $ex->subject ? $ex->subject->name : ($ex->subject_id ?? null),
                    'niveau'          => $ex->niveau,
                    'classe'          => $ex->classe,
                    
                    // Chemins d'accès complets pour les fichiers
                    'exercise_path'   => $ex->file_path 
                        ? asset('storage/' . $ex->file_path) 
                        : null,
                    
                    'correction_path' => $ex->correction_path 
                        ? asset('storage/' . $ex->correction_path) 
                        : null,
                    
                    'is_active'       => (bool) $ex->active,
                    'created_at'      => $ex->created_at ? $ex->created_at->format('d/m/Y') : now()->format('d/m/Y'),
                ];
            });

            return response()->json([
                'status' => true,
                'data'   => $data
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Erreur lors de la récupération des exercices',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================
     * CRÉATION ET ENREGISTREMENT D'UN EXERCICE
     * ==========================================
     */
    public function store(Request $request)
    {
        // Validation stricte alignée sur les champs envoyés par Flutter
        $request->validate([
            'titre'           => 'required|string|max:255',
            'matiere'         => 'required', 
            'niveau'          => 'required|string',
            'classe'          => 'required|string',
            'exercise_file'   => 'required|file|mimes:pdf,doc,docx,jpg,png|max:51200',
            'correction_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,png|max:51200',
        ]);

        try {
            $user = Auth::user();
            $userId = $user->id;

            // Règle d'activation : Actif par défaut si Admin, sinon en attente pour les profs
            $isActive = ($user->role === 'admin') ? 1 : 0;

            // Gestion sécurisée du profil enseignant rattaché
            $teacher = DB::table('teachers')->where('user_id', $userId)->first();

            if (!$teacher) {
                $teacherId = DB::table('teachers')->insertGetId([
                    'user_id'    => $userId,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $teacherId = $teacher->id;
            }

            /**
             * 🛠️ GESTION DU SUBJECT_ID (Texte ou ID Numérique)
             * Gère le cas où l'application mobile envoie le nom (ex: "maths fine") au lieu de l'ID numérique
             */
            $subjectId = $request->matiere;

            if (!is_numeric($subjectId)) {
                // On cherche l'ID dans la table 'subjects' grâce au nom de la matière
                $subject = DB::table('subjects')
                    ->where('name', 'LIKE', trim($subjectId))
                    ->first();

                if ($subject) {
                    $subjectId = $subject->id;
                } else {
                    // Si la matière textuelle n'existe pas, on la crée dynamiquement
                    $subjectId = DB::table('subjects')->insertGetId([
                        'name'       => trim($request->matiere),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Upload des fichiers sur le disque public
            $exercisePath = $request->file('exercise_file')->store('exercises', 'public');
            $correctionPath = null;

            if ($request->hasFile('correction_file')) {
                $correctionPath = $request->file('correction_file')->store('corrections', 'public');
            }

            // Insertion en base de données avec l'ID résolu et valide
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

            return response()->json([
                'status'  => true,
                'message' => 'Exercice enregistré avec succès',
                'data'    => $exercise
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Erreur lors de l’enregistrement',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================
     * SUPPRESSION D'UN EXERCICE ET DE SES PDF
     * ==========================================
     */
    public function destroy($id)
    {
        try {
            $exercise = Exercise::findOrFail($id);
            
            // Nettoyage des fichiers PDF stockés sur le serveur avant la suppression de la ligne
            if ($exercise->file_path) {
                Storage::disk('public')->delete($exercise->file_path);
            }
            if ($exercise->correction_path) {
                Storage::disk('public')->delete($exercise->correction_path);
            }

            $exercise->delete();

            return response()->json([
                'status'  => true,
                'message' => 'Exercice supprimé avec succès'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Erreur lors de la suppression',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}