<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdConfiguration;

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
            ]
        ]);
    }
}