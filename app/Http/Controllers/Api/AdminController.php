<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Book;
use App\Models\Institution;
use App\Models\User;

class AdminController extends Controller
{
    /**
     * Validation d’un cours proposé
     */
    public function validateCourse($id)
    {
        $course = Course::findOrFail($id);
        $course->active = true;
        $course->save();

        return response()->json([
            'status' => true,
            'message' => 'Cours validé et rendu public',
        ]);
    }

    /**
     * Validation d’un livre
     */
    public function validateBook($id)
    {
        $book = Book::findOrFail($id);
        $book->active = true;
        $book->save();

        return response()->json([
            'status' => true,
            'message' => 'Livre validé',
        ]);
    }

    public function createInstitution(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:university,training_center',
        ]);

        $institution = Institution::create([
            'name'        => $request->name,
            'description' => $request->description,
            'type'        => $request->type,
            'created_by'  => $request->user()->id,
        ]);

        return response()->json([
            'status' => true,
            'institution' => $institution,
        ], 201);
    }

    public function listTeachers()
    {
        return response()->json(
            User::where('role', 'teacher')->get()
        );
    }

    public function deleteTeacher($id)
    {
        $teacher = User::where('role', 'teacher')->findOrFail($id);
        $teacher->delete();

        return response()->json([
            'status' => true,
            'message' => 'Enseignant supprimé',
        ]);
    }
}
