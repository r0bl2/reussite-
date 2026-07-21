<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'username',
        'email',
        'password',
        'role',
        'telephone',
        'type_lycee',
        'level',
        'class',
        'matieres',
    ];                                                                                

    protected $hidden = [
        'password',
    ];

    protected $casts = [
    'matieres' => 'array',
    ];

    public function student()
    {
        return $this->hasOne(Student::class);
    }



    public function studentProfile()
{
    return $this->hasOne(Student::class);
} 

public function teacherProfile()
{
    return $this->hasOne(Teacher::class, 'user_id', 'id');
}


}
