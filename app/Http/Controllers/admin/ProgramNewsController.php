<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramNews;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProgramNewsController extends Controller
{
    public function index()
    {
        $newsItems = ProgramNews::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(15);

        return view('content.admin.program-news.index', compact('newsItems'));
    }

    public function create()
    {
        $news = new ProgramNews(['published_at' => now()]);

        return view('content.admin.program-news.form', [
            'news' => $news,
            'categories' => ProgramNews::CATEGORIES,
            'regions' => ProgramNews::REGIONS,
        ]);
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:50000'],
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(ProgramNews::CATEGORIES))],
            'region' => ['required', 'string', 'in:'.implode(',', array_keys(ProgramNews::REGIONS))],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'published_at' => ['required', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $image = $attributes['image'];
        unset($attributes['image']);
        $attributes['image_path'] = $this->storeImage($image);
        $attributes['is_published'] = $request->boolean('is_published');
        ProgramNews::query()->create($attributes);

        return redirect()->route('admin.program-news.index')->with('status', 'L’actualité a été créée.');
    }

    public function edit(ProgramNews $news)
    {
        return view('content.admin.program-news.form', [
            'news' => $news,
            'categories' => ProgramNews::CATEGORIES,
            'regions' => ProgramNews::REGIONS,
        ]);
    }

    public function update(Request $request, ProgramNews $news)
    {
        $attributes = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'summary' => ['required', 'string', 'max:500'],
            'content' => ['required', 'string', 'max:50000'],
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(ProgramNews::CATEGORIES))],
            'region' => ['required', 'string', 'in:'.implode(',', array_keys(ProgramNews::REGIONS))],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'published_at' => ['required', 'date'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $image = $attributes['image'] ?? null;
        unset($attributes['image']);
        $previousImage = $news->image_path;

        if ($image) {
            $attributes['image_path'] = $this->storeImage($image);
        }

        $attributes['is_published'] = $request->boolean('is_published');
        $news->update($attributes);

        if ($image) {
            $this->deleteImage($previousImage);
        }

        return redirect()->route('admin.program-news.index')->with('status', 'L’actualité a été mise à jour.');
    }

    public function togglePublication(ProgramNews $news)
    {
        $news->is_published = !$news->is_published;
        $news->save();

        return redirect()->route('admin.program-news.index')
            ->with('status', $news->is_published ? 'L’actualité a été publiée.' : 'L’actualité a été dépubliée.');
    }

    public function destroy(ProgramNews $news)
    {
        $imagePath = $news->image_path;
        $news->delete();
        $this->deleteImage($imagePath);

        return redirect()->route('admin.program-news.index')->with('status', 'L’actualité a été supprimée.');
    }

    private function storeImage(UploadedFile $image): string
    {
        $path = $image->store('program-news', 'public');

        if (!is_string($path) || $path === '') {
            throw new RuntimeException('Impossible d’enregistrer l’image de l’actualité.');
        }

        return $path;
    }

    private function deleteImage(string $path): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($path) && !$disk->delete($path)) {
            throw new RuntimeException('Impossible de supprimer l’image de l’actualité.');
        }
    }
}
