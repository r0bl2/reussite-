<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdConfiguration;
use Illuminate\Http\Request;

class AdConfigController extends Controller
{
    public function index()
    {
        // Récupère la configuration en BDD ou prend la première ligne
        $config = AdConfiguration::first();

        $isProduction = config('app.env') === 'production';

        // IDs de test officiels AdMob par défaut (si non définis en BDD/env)
        $defaultBanner = 'ca-app-pub-3940256099942544/6300978111';
        $defaultInterstitial = 'ca-app-pub-3940256099942544/1033173712';

        return response()->json([
            'show_ads' => $config ? $config->show_ads : true,
            'ads' => [
                'banner_ad_unit_id' => $isProduction
                    ? ($config->banner_ad_unit_id ?? config('services.admob.banner_id'))
                    : $defaultBanner,

                'interstitial_ad_unit_id' => $isProduction
                    ? ($config->interstitial_ad_unit_id ?? config('services.admob.interstitial_id'))
                    : $defaultInterstitial,
            ],
            'tutor_contact_phone' => $config->tutor_contact_phone ?? null,
        ]);
    }

    /**
     * Mise à jour de la configuration (réservé à l'admin).
     * Permet notamment de saisir le numéro de contact affiché
     * sur la liste des répétiteurs validés, au lieu d'un numéro en dur.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        if (!$user || mb_strtolower(trim($user->role)) !== 'admin') {
            return response()->json([
                'status'  => false,
                'message' => 'Accès non autorisé',
            ], 403);
        }

        $request->validate([
            'show_ads'                 => 'sometimes|boolean',
            'banner_ad_unit_id'        => 'sometimes|nullable|string',
            'interstitial_ad_unit_id'  => 'sometimes|nullable|string',
            'tutor_contact_phone'      => 'sometimes|nullable|string|max:30',
        ]);

        // Table de config à ligne unique : on récupère la première ligne ou on la crée
        $config = AdConfiguration::first();

        if (!$config) {
            $config = new AdConfiguration();
        }

        $config->fill($request->only([
            'show_ads',
            'banner_ad_unit_id',
            'interstitial_ad_unit_id',
            'tutor_contact_phone',
        ]));

        $config->save();

        return response()->json([
            'status'  => true,
            'message' => 'Configuration mise à jour avec succès',
            'data'    => $config,
        ]);
    }
}