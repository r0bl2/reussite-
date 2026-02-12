<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    // Relations
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
}
