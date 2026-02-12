<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type_lycee',
        'niveau',
        'classe',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
