<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'teacher_id',
        'subject_id',
        'title',
        'description',
        'file_path',
        'level',
        'class',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $with = ['subject']; // 🔥 IMPORTANT (auto load)

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}