<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', 'teacher');
        if ($request->filled('q')) {
            $query->where(fn ($q) => $q->where('username', 'like', '%' . $request->q . '%')->orWhere('email', 'like', '%' . $request->q . '%'));
        }
        $users = $query->latest()->paginate(20);
        $users->appends($request->query());

        return view('admin.users.index', ['users' => $users, 'title' => 'Gestion des Enseignants', 'teacher' => true]);
    }
}
