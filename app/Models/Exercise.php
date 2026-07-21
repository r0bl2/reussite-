<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    // C'est ICI que Laravel décide ce qu'il a le droit d'écrire en base
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'title',
        'niveau',
        'classe',
        'file_path',
        'correction_path',
        'active',
    ];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        // Si ton modèle s'appelle 'Subject', ajuste le nom de la classe si nécessaire
        return $this->belongsTo(Subject::class, 'subject_id'); 
    }
}