<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Récupère toutes les bannières actives.
     */
    public function index(): JsonResponse
    {
        $banners = Banner::where('is_active', true)
            ->orderBy('order', 'asc')
            ->get();

        // Transformer les chemins relatifs en URL complètes
        $banners->transform(function ($banner) {
            return $this->formatMediaUrl($banner);
        });

        return response()->json($banners);
    }

    /**
     * Enregistre une nouvelle bannière en base de données.
     */
    public function store(Request $request): JsonResponse
    {
        // 1. Validation des données reçues
        $validated = $request->validate([
            'title'      => 'nullable|string|max:255',
            'type'       => 'required|in:image,video',
            'media'      => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:10240', // Si vous envoyez un fichier
            'media_url'  => 'nullable|string', // Si vous envoyez un lien direct
            'target_url' => 'nullable|string',
            'order'      => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        // 2. Traitement du fichier média (si un fichier est uploadé)
        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('banners', 'public');
            $validated['media_url'] = $path; // Enregistre "banners/nom_du_fichier.png"
        }

        // 3. Valeurs par défaut indispensables
        $validated['is_active'] = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);
        $validated['order']     = (int) $request->input('order', 0);

        // 4. SAUVEGARDE EFFECTIVE EN BASE DE DONNÉES SQL
        $banner = Banner::create($validated);

        // Reconstituer l'URL complète avant de renvoyer la réponse
        $banner = $this->formatMediaUrl($banner);

        return response()->json([
            'message' => 'Bannière enregistrée avec succès !',
            'data'    => $banner
        ], 201);
    }

    /**
     * Modifie une bannière existante.
     */
    public function update(Request $request, $id): JsonResponse
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['message' => 'Bannière non trouvée'], 404);
        }

        // 1. Validation des données reçues
        $validated = $request->validate([
            'title'      => 'nullable|string|max:255',
            'type'       => 'required|in:image,video',
            'media'      => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:10240',
            'media_url'  => 'nullable|string',
            'target_url' => 'nullable|string',
            'order'      => 'nullable|integer',
            'is_active'  => 'nullable|boolean',
        ]);

        // 2. Traitement du nouveau fichier média
        if ($request->hasFile('media')) {
            // Supprimer l'ancien fichier s'il existait en local
            if ($banner->media_url && !str_starts_with($banner->media_url, 'http')) {
                Storage::disk('public')->delete($banner->media_url);
            }

            // Stocker le nouveau fichier
            $path = $request->file('media')->store('banners', 'public');
            $validated['media_url'] = $path;
        }

        // 3. Cast explicite des valeurs booléennes et entières si présentes
        if ($request->has('is_active')) {
            $validated['is_active'] = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->has('order')) {
            $validated['order'] = (int) $request->input('order');
        }

        // 4. MISE À JOUR EN BASE DE DONNÉES
        $banner->update($validated);

        // Reconstituer l'URL complète avant de renvoyer la réponse
        $banner = $this->formatMediaUrl($banner);

        return response()->json([
            'message' => 'Bannière mise à jour avec succès !',
            'data'    => $banner
        ], 200);
    }

    /**
     * Supprime une bannière.
     */
    public function destroy($id): JsonResponse
    {
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json(['message' => 'Bannière non trouvée'], 404);
        }

        // Supprimer le fichier physique du stockage s'il existe
        if ($banner->media_url && !str_starts_with($banner->media_url, 'http')) {
            Storage::disk('public')->delete($banner->media_url);
        }

        $banner->delete();

        return response()->json(['message' => 'Bannière supprimée']);
    }

    /**
     * Reconstitue le chemin d'accès au fichier sous forme d'URL complète.
     */
    private function formatMediaUrl(Banner $banner): Banner
    {
        if ($banner->media_url) {
            $path = ltrim($banner->media_url, '/');
            if (!str_starts_with($path, 'http://') && !str_starts_with($path, 'https://')) {
                if (!str_starts_with($path, 'storage/')) {
                    $path = 'storage/' . $path;
                }
                $banner->media_url = asset($path);
            }
        }
        return $banner;
    }
}