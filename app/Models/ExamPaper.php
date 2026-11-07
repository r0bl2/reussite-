<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamPaper extends Model
{
    protected $fillable = ['title', 'description', 'subject', 'level', 'file_path', 'created_by', 'status', 'rejection_reason', 'reviewed_by', 'reviewed_at'];

    protected $casts = ['reviewed_at' => 'datetime'];

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
