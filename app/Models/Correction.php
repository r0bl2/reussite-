<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Correction extends Model
{
    protected $fillable = [
        'exercise_id',
        'file_path',
        'is_active'
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
