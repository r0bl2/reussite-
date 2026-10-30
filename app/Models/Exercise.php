<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = ['teacher_id', 'subject_id', 'title', 'niveau', 'classe', 'file_path', 'correction_path', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }
}
