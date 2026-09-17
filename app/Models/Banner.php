<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    /**
     * Les attributs autorisés lors d'une création / mise à jour.
     */
    protected $fillable = [
        'title',
        'type',        
        'media_url',   
        'target_url',  
        'is_active',   
        'order',       
    ];

    /**
     * Conversion automatique des types de données.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'order'     => 'integer',
    ];

    /**
     *  attributs virtuels à inclure automatiquement dans les réponses JSON API.
     */
    protected $appends = [
        'full_media_url',
    ];

    /**
     * Accessor pour récupérer automatiquement l'URL complète du média.
     * Utilisation dans Flutter/JSON : $banner->full_media_url
     */
    public function getFullMediaUrlAttribute(): ?string
    {
        if (empty($this->media_url)) {
            return null;
        }

        if (filter_var($this->media_url, FILTER_VALIDATE_URL)) {
            return $this->media_url;
        }

        $path = ltrim($this->media_url, '/');
        if (!str_starts_with($path, 'storage/')) {
            $path = 'storage/' . $path;
        }

        return asset($path);
    }
}