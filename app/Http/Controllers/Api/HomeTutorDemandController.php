<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomeTutorDemand;
use App\Models\Teacher;
use Illuminate\Support\Facades\Validator;

class HomeTutorDemandController extends Controller
{
    /**
     * 1. SOUMETTRE UNE DEMANDE (Pour l'enseignant)
     */
    public function store(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Utilisateur non authentifié.'
            ], 401);
        }

        // Recherche robuste du profil enseignant via user_id
        $teacher = Teacher::where('user_id', $user->id)->first();

        // Sécurisation du rôle ET de l'existence du profil
        if ($user->role !== 'teacher' || !$teacher) {
            return response()->json([
                'status' => false,
                'message' => 'Accès refusé. Seuls les enseignants avec un profil valide peuvent postuler.'
            ], 403);
        }

        // Validation des données du formulaire et des fichiers
        $validator = Validator::make($request->all(), [
            'last_name' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'phone_number' => 'required|string|max:20',
            'levels' => 'required|array|min:1',
            'subjects' => 'required|array|min:1',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120',
            'diploma' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Erreur de validation',
                'errors' => $validator->errors()
            ], 422);
        }

        // Vérifier si l'enseignant a déjà une demande en attente
        $existingDemand = HomeTutorDemand::where('teacher_id', $teacher->id)
            ->where('status', 'pending')
            ->first();

        if ($existingDemand) {
            return response()->json([
                'status' => false,
                'message' => 'Vous avez déjà une demande de répétition en cours d\'examen.'
            ], 422);
        }

        // Sauvegarde des fichiers dans le dossier storage/app/public/tutors
        $photoFile = $request->file('photo');
        $diplomaFile = $request->file('diploma');

        if (!$photoFile || !$diplomaFile) {
            return response()->json([
                'status' => false,
                'message' => 'Fichiers manquants.'
            ], 422);
        }

        $photoPath = $photoFile->store('tutors/photos', 'public');
        $diplomaPath = $diplomaFile->store('tutors/diplomas', 'public');

        // Création de la demande
        $demand = HomeTutorDemand::create([
            'teacher_id' => $teacher->id,
            'last_name' => $request->last_name,
            'first_name' => $request->first_name,
            'phone_number' => $request->phone_number,
            'photo_path' => $photoPath,
            'diploma_path' => $diplomaPath,
            'levels' => $request->levels,
            'subjects' => $request->subjects,
            'status' => 'pending',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Votre demande a été soumise avec succès et est en attente de validation.',
            'data' => $demand
        ], 201);
    }

    /**
     * 2. LISTER LES DEMANDES EN ATTENTE (Pour l'admin)
     */
    public function indexAdmin()
    {
        // Récupère toutes les demandes en attente avec les infos de l'enseignant de base
        $demands = HomeTutorDemand::with('teacher.user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        // On adapte les chemins de fichiers pour renvoyer des URLs complètes et lisibles
        $formattedDemands = $demands->map(function($demand) {
            return [
                'id' => $demand->id,
                'teacher_id' => $demand->teacher_id,
                'last_name' => $demand->last_name,
                'first_name' => $demand->first_name,
                'phone_number' => $demand->phone_number,
                'levels' => $demand->levels,
                'subjects' => $demand->subjects, 
                'photo_url' => asset('storage/' . $demand->photo_path),
                'diploma_url' => asset('storage/' . $demand->diploma_path),
                'status' => $demand->status,
                'created_at' => $demand->created_at ? $demand->created_at->format('d/m/Y H:i') : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formattedDemands
        ], 200);
    }

    /**
     * 3. VALIDER OU REJETER UNE DEMANDE (Pour l'admin)
     */
    public function validateDemand(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'admin_comment' => 'required_if:status,rejected|string|nullable'
        ]);

        $demand = HomeTutorDemand::find($id);

        if (!$demand) {
            return response()->json([
                'status' => false,
                'message' => 'Demande introuvable.'
            ], 404);
        }

        // Mise à jour de la demande
        $demand->status = $request->status;
        if ($request->status === 'rejected') {
            $demand->admin_comment = $request->admin_comment;
        } else {
            $demand->admin_comment = null;
        }
        
        $demand->save();

        return response()->json([
            'status' => true,
            'message' => 'La demande a été mise à jour avec succès (' . $request->status . ').',
            'data' => $demand
        ], 200);
    }

    /**
     * 4. RÉCUPÉRER LE STATUT DE LA DEMANDE CONNECTÉE (Pour l'enseignant)
     */
    public function getMyDemandStatus(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Non authentifié.'
            ], 401);
        }

        $teacher = Teacher::where('user_id', $user->id)->first();

        if (!$teacher) {
            return response()->json([
                'status' => false,
                'message' => 'Profil enseignant introuvable.'
            ], 404);
        }

        // Récupérer la toute dernière demande de cet enseignant
        $demand = HomeTutorDemand::where('teacher_id', $teacher->id)
            ->latest()
            ->first();

        if (!$demand) {
            return response()->json([
                'status' => true,
                'data' => null
            ], 200);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'id' => $demand->id,
                'status' => $demand->status,
                'admin_comment' => $demand->admin_comment,
            ]
        ], 200);
    }

    /**
     * 5. HISTORIQUE DES DEMANDES TRAITÉES (Pour l'admin)
     */
    public function indexHistoryAdmin()
    {
        $demands = HomeTutorDemand::whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->get();

        $formattedDemands = $demands->map(function($demand) {
            return [
                'id' => $demand->id,
                'teacher_id' => $demand->teacher_id,
                'last_name' => $demand->last_name,
                'first_name' => $demand->first_name,
                'phone_number' => $demand->phone_number,
                'levels' => $demand->levels,
                'subjects' => $demand->subjects, 
                'photo_url' => asset('storage/' . $demand->photo_path),
                'diploma_url' => asset('storage/' . $demand->diploma_path),
                'status' => $demand->status,
                'admin_comment' => $demand->admin_comment,
                'processed_at' => $demand->updated_at ? $demand->updated_at->format('d/m/Y H:i') : null,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formattedDemands
        ], 200);
    }

    /**
     * 6. LISTER LES RÉPÉTITEURS VALIDES (Pour les Étudiants / Parents)
     */
    public function indexApprovedPublic()
    {
        $approvedTutors = HomeTutorDemand::where('status', 'approved')
            ->latest()
            ->get();

        $formattedTutors = $approvedTutors->map(function($tutor) {
            return [
                'id' => $tutor->id,
                'last_name' => $tutor->last_name,
                'first_name' => $tutor->first_name,
                'phone_number' => "+228 90 00 00 00",
                'levels' => $tutor->levels,
                'subjects' => $tutor->subjects, 
                'photo_url' => asset('storage/' . $tutor->photo_path),
            ];
        });

        return response()->json([
            'status' => true,
            'data' => $formattedTutors
        ], 200);
    }
}