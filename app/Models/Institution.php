<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    protected $fillable = [
        'name',
        'city',
        'type'
    ];

    public function trainings()
    {
        return $this->hasMany(Training::class);
    }
}
