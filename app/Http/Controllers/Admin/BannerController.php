<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::orderBy('order')->paginate(15);

        return view('admin.banners.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banners.form', ['banner' => new Banner()]);
    }

    public function store(Request $request)
    {
        $data = $this->rules($request);

        if ($request->hasFile('media')) {
            $data['media_url'] = $request->file('media')->store('banners', 'public');
        }
        unset($data['media']);
        $data['is_active'] = $request->boolean('is_active');
        $data['order'] = (int) $request->input('order', 0);

        Banner::create($data);

        return redirect()->route('banners.index')->with('success', 'Bannière créée.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', compact('banner'));
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->rules($request);

        if ($request->hasFile('media')) {
            if ($banner->media_url && !str_starts_with($banner->media_url, 'http')) {
                Storage::disk('public')->delete($banner->media_url);
            }
            $data['media_url'] = $request->file('media')->store('banners', 'public');
        }
        unset($data['media']);
        $data['is_active'] = $request->boolean('is_active');
        $data['order'] = (int) $request->input('order', 0);

        $banner->update($data);

        return redirect()->route('banners.index')->with('success', 'Bannière modifiée.');
    }

    public function destroy(Banner $banner)
    {
        if ($banner->media_url && !str_starts_with($banner->media_url, 'http')) {
            Storage::disk('public')->delete($banner->media_url);
        }
        $banner->delete();

        return back()->with('success', 'Bannière supprimée.');
    }

    private function rules(Request $request): array
    {
        return $request->validate([
            'title'      => 'nullable|string|max:255',
            'type'       => 'required|in:image,video',
            'media'      => 'nullable|file|mimes:jpeg,png,jpg,gif,mp4|max:10240',
            'media_url'  => 'nullable|string',
            'target_url' => 'nullable|string',
            'order'      => 'nullable|integer',
        ]);
    }
}
