<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SubjectController extends Controller
{
    /**
     * Liste des matières.
     */
    public function index(Request $request)
    {
        try {
            $query = Subject::orderBy('name', 'asc');

            if ($request->filled('type_lycee')) {
                $query->where('type_lycee', $request->type_lycee);
            }

            return response()->json([
                'status' => true,
                'data' => $query->get()
            ], 200);

        } catch (\Exception $e) {

            Log::error('Erreur index SubjectController : ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Impossible de récupérer les matières.'
            ], 500);
        }
    }

    /**
     * Création d'une matière.
     */
    public function store(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Action non autorisée. Seul l’administrateur peut créer une matière.'
            ], 403);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects')->where(function ($query) use ($request) {
                    return $query->where('type_lycee', $request->type_lycee);
                }),
            ],
            'type_lycee' => 'required|string|in:college,lycee_moderne,lycee_technique',
        ], [
            'name.required' => 'Le nom de la matière est obligatoire.',
            'name.unique' => 'Cette matière existe déjà pour ce type d’établissement.',
            'name.max' => 'Le nom est trop long.',
            'type_lycee.required' => 'Le type d’établissement est obligatoire.',
            'type_lycee.in' => 'Le type sélectionné est invalide.',
        ]);

        try {

            $subject = Subject::create([
                'name' => trim($validated['name']),
                'type_lycee' => $validated['type_lycee'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Matière créée avec succès !',
                'data' => $subject
            ], 201);

        } catch (\Exception $e) {

            Log::error('Erreur store SubjectController : ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ], 500);
        }
    }

    /**
     * Modification d'une matière.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Action non autorisée. Seul l’administrateur peut modifier une matière.'
            ], 403);
        }

        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json([
                'status' => false,
                'message' => 'Matière introuvable.'
            ], 404);
        }

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('subjects')
                    ->where(function ($query) use ($request) {
                        return $query->where('type_lycee', $request->type_lycee);
                    })
                    ->ignore($subject->id),
            ],
            'type_lycee' => 'required|string|in:college,lycee_moderne,lycee_technique',
        ]);

        try {

            $subject->update([
                'name' => trim($validated['name']),
                'type_lycee' => $validated['type_lycee'],
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Matière mise à jour avec succès !',
                'data' => $subject->fresh()
            ]);

        } catch (\Exception $e) {

            Log::error('Erreur update SubjectController : ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suppression d'une matière.
     */
    public function destroy(Request $request, $id)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Action non autorisée. Seul l’administrateur peut supprimer une matière.'
            ], 403);
        }

        $subject = Subject::find($id);

        if (!$subject) {
            return response()->json([
                'status' => false,
                'message' => 'Matière introuvable.'
            ], 404);
        }

        try {

            if (
                $subject->courses()->exists() ||
                $subject->summaries()->exists() ||
                $subject->exercises()->exists() ||
                $subject->books()->exists()
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Impossible de supprimer cette matière car elle possède déjà des contenus associés.'
                ], 400);
            }

            $subject->delete();

            return response()->json([
                'status' => true,
                'message' => 'Matière supprimée avec succès !'
            ]);

        } catch (\Exception $e) {

            Log::error('Erreur destroy SubjectController : ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}