<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeTutorDemand extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'last_name',
        'first_name',
        'phone_number',
        'photo_path',
        'diploma_path',
        'levels',
        'subjects',
        'status',
        'admin_comment'
    ];

    /**
     * ⚙️ Cast des attributs JSON en tableaux PHP utilisables
     */
    protected $casts = [
        'levels' => 'array',
        'subjects' => 'array',
    ];

    /**
     * Relation inverse vers l'enseignant
     */
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }
}