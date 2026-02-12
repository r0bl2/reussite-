<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    protected $fillable = [
        'subject_id',
        'title',
        'description',
        'file_path',
        'is_active'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}
