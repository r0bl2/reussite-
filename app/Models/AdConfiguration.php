<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'show_ads',
        'banner_ad_unit_id',
        'interstitial_ad_unit_id',
        'tutor_contact_phone',
    ];

    protected $casts = [
        'show_ads' => 'boolean',
    ];
}