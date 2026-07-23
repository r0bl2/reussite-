<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Teacher extends Model
{
    protected $fillable = [

        'user_id',

        'telephone',

        'type_lycee',

        'level',

        'class',

        'matieres',

        'diplome',

        'photo',
    ];

    protected $casts = [
        'matieres' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    public function courses()
    {
        return $this->hasMany(
            Course::class,
            'teacher_id',
            'id'
        );
    }


    /**
     * 📚 Les matières choisies lors de l'inscription (Max 3)
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teacher');
    }

    /**
     * 🏠 La demande de répétiteur à domicile de l'enseignant
     */
    public function homeTutorDemand()
    {
        return $this->hasOne(HomeTutorDemand::class);
    }
}