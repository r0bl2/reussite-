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
     * =========================
     * INSCRIPTION
     * =========================
     */
    public function registerApi(Request $request)
    {
        $request->validate([
            'username' => 
                'required|string|unique:users,username',

            'email' => 
                'nullable|email|unique:users,email',

            'password' => 
                'required|min:6',

            'role' => 
                'required|in:student,teacher,parent,other',

            // TELEPHONE OBLIGATOIRE POUR ENSEIGNANT + UNIQUE
            'telephone' => 
                'required_if:role,teacher|nullable|unique:teachers,telephone|string|max:20',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role,
        ]);

        /*
        |----------------------------
        | STUDENT
        |----------------------------
        */
        if ($user->role === 'student') {
            Student::create([
                'user_id'    => $user->id,
                'type_lycee' => $request->type_lycee,
                'niveau'     => $request->level,
                'classe'     => $request->class,
            ]);
        }

        /*
        |----------------------------
        | TEACHER
        |----------------------------
        */
        if ($user->role === 'teacher') {
            Teacher::create([
                'user_id'     => $user->id,
                'telephone'   => $request->telephone,
                'type_lycee'  => $request->type_lycee,
                'level'       => $request->level,
                'class'       => $request->class,
                'matieres'    => $request->matieres,
                'diplome'     => null, // Mis à null par défaut si votre table SQL requiert le champ
                'photo'       => null, // Mis à null par défaut si votre table SQL requiert le champ
            ]);
        }

        return response()->json([
            'status'   => true,
            'message'  => 'Inscription réussie',
            'redirect' => 'login'
        ], 201);
    }

    /**
     * =========================
     * LOGIN
     * =========================
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

        $classe = null;
        $niveau = null;
        $type_lycee = null;

        if ($user->role === 'student') {
            $profile = $user->studentProfile;
            if ($profile) {
                $classe = $profile->classe;
                $niveau = $profile->niveau;
                $type_lycee = $profile->type_lycee;
            }
        }

        $user->tokens()->delete();

        $token = $user->createToken('api_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Connexion réussie',
            'token'   => $token,
            'role'    => $user->role,
            'user'    => [
                'id'         => $user->id,
                'username'   => $user->username,
                'email'      => $user->email,
                'classe'     => $classe,
                'niveau'     => $niveau,
                'type_lycee' => $type_lycee,
            ],
        ]);
    }

    /**
     * ==========================================
     * 🆕 RÉCUPÉRER LE PROFIL ENSEIGNANT CONNECTÉ
     * ==========================================
     */
    public function getTeacherProfile(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'teacher') {
            return response()->json([
                'status' => false,
                'message' => 'Accès interdit. Cet utilisateur n\'est pas un enseignant.'
            ], 403);
        }

        $user->load('teacherProfile'); 

        return response()->json([
            'status' => true,
            'user'   => $user
        ], 200);
    }
}