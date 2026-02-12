<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * INSCRIPTION
     */
    public function registerApi(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:users,username',
            'email'    => 'nullable|email|unique:users,email',
            'password' => 'required|min:6',
            'role'     => 'required|in:élève,enseignant,parent,autre',
        ]);

        $user = User::create([
            'username'    => $request->username,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'school_type' => $request->school_type,
            'level'       => $request->level,
            'class'       => $request->class,
        ]);

        // Création profil étudiant
        if ($user->role === 'student') {
            Student::create([
                'user_id'    => $user->id,
                'type_lycee' => $request->school_type,
                'niveau'     => $request->level,
                'classe'     => $request->class,
            ]);
        }

        // Création profil enseignant
        if ($user->role === 'teacher') {
            Teacher::create([
                'user_id' => $user->id,
            ]);
        }

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Inscription réussie',
            'token'   => $token,
            'role'    => $user->role,
            'user'    => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
            ],
        ], 201);
    }

    /**
     * CONNEXION (ADMIN / TEACHER / STUDENT / ETC)
     */
    public function loginApi(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Identifiants invalides'],
            ]);
        }

        // Supprimer anciens tokens
        $user->tokens()->delete();

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Connexion réussie',
            'token'   => $token,
            'role'    => $user->role, 
            'user'    => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
            ],
        ]);
    }
}
