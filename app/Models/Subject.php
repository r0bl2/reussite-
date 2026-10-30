<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'type_lycee',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function summaries()
    {
        return $this->hasMany(Summary::class);
    }

    public function exercises()
    {
        return $this->hasMany(Exercise::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'subject_teacher');
    }
}
