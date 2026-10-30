<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $query = News::query();
        if ($request->filled('q')) {
            $query->where('title', 'like', '%' . $request->q . '%');
        }
        $news = $query->latest()->paginate(15);
        $news->appends($request->query());

        return view('admin.news.index', compact('news'));
    }

    public function create()
    {
        return view('admin.news.form', ['item' => new News()]);
    }

    public function store(Request $request)
    {
        $data = $this->rules($request);

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('news/covers', 'public');
        }
        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('news/attachments', 'public');
        }
        $data['is_published'] = $request->boolean('is_published');
        $data['admin_id'] = $request->user()->id;

        News::create($data);

        return redirect()->route('news.index')->with('success', 'Actualité créée.');
    }

    public function edit(News $news)
    {
        return view('admin.news.form', ['item' => $news]);
    }

    public function update(Request $request, News $news)
    {
        $data = $this->rules($request);

        foreach (['cover_image' => 'news/covers', 'attachment' => 'news/attachments'] as $field => $dir) {
            if ($request->hasFile($field)) {
                if ($news->$field) {
                    Storage::disk('public')->delete($news->$field);
                }
                $data[$field] = $request->file($field)->store($dir, 'public');
            }
        }
        $data['is_published'] = $request->boolean('is_published');

        $news->update($data);

        return redirect()->route('news.index')->with('success', 'Actualité modifiée.');
    }

    public function destroy(News $news)
    {
        foreach (['cover_image', 'attachment'] as $field) {
            if ($news->$field) {
                Storage::disk('public')->delete($news->$field);
            }
        }
        $news->delete();

        return back()->with('success', 'Actualité supprimée.');
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'title'       => 'required|string|max:255',
            'content'     => 'required|string',
            'category'    => 'nullable|string|max:100',
            'cover_image' => 'nullable|image|max:5120',
            'attachment'  => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);
    }
}
