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
    ];                                                                                

    protected $hidden = [
        'password',
    ];

    public function student()
    {
        return $this->hasOne(Student::class);
    }
}
