<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::where('is_published', true)->latest();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        return response()->json($query->paginate(15));
    }

    public function show(News $news)
    {
        return response()->json($news);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category'    => 'nullable|string|max:100',
            'cover_image' => 'nullable|image|max:5120',
            'attachment'  => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'is_published'=> 'sometimes|boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('news/covers', 'public');
        }

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('news/attachments', 'public');
        }

        $validated['admin_id'] = $request->user()->id;

        $news = News::create($validated);

        return response()->json($news, 201);
    }

    public function update(Request $request, News $news)
    {
        $this->authorizeAdmin($request);

        $validated = $request->validate([
            'title'       => 'sometimes|required|string|max:255',
            'content'     => 'sometimes|required|string',
            'category'    => 'nullable|string|max:100',
            'cover_image' => 'nullable|image|max:5120',
            'attachment'  => 'nullable|file|mimes:pdf,doc,docx|max:10240',
            'is_published'=> 'sometimes|boolean',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($news->cover_image) {
                Storage::disk('public')->delete($news->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('news/covers', 'public');
        }

        if ($request->hasFile('attachment')) {
            if ($news->attachment) {
                Storage::disk('public')->delete($news->attachment);
            }
            $validated['attachment'] = $request->file('attachment')->store('news/attachments', 'public');
        }

        $news->update($validated);

        return response()->json($news);
    }

    public function destroy(Request $request, News $news)
    {
        $this->authorizeAdmin($request);

        if ($news->cover_image) {
            Storage::disk('public')->delete($news->cover_image);
        }
        if ($news->attachment) {
            Storage::disk('public')->delete($news->attachment);
        }

        $news->delete();

        return response()->json(['message' => 'Actualité supprimée']);
    }

    private function authorizeAdmin(Request $request): void
    {
        if ($request->user()->role !== 'admin') {
            abort(403, 'Seul un administrateur peut effectuer cette action.');
        }
    }
}