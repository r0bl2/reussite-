<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = ['subject_id', 'teacher_id', 'title', 'author', 'description', 'file_path', 'price', 'active'];

    protected $casts = ['active' => 'boolean'];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
