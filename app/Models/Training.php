<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Training extends Model
{
    protected $fillable = [
        'institution_id',
        'name',
        'level'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
