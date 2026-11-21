<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\ProgramNews;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramNewsPage extends Controller
{
    public function index(Request $request)
    {
        $years = ProgramNews::query()
            ->published()
            ->get(['published_at'])
            ->map(fn (ProgramNews $news) => $news->published_at->year)
            ->unique()
            ->sortDesc()
            ->values();

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', Rule::in(array_keys(ProgramNews::CATEGORIES))],
            'region' => ['nullable', Rule::in(array_keys(ProgramNews::REGIONS))],
            'year' => ['nullable', 'integer', Rule::in($years->all())],
        ]);

        $newsItems = ProgramNews::query()
            ->published()
            ->when(
                filled($filters['q'] ?? null),
                fn ($query) => $query->where(function ($query) use ($filters) {
                    $search = '%'.$filters['q'].'%';
                    $query->where('title', 'like', $search)
                        ->orWhere('summary', 'like', $search)
                        ->orWhere('content', 'like', $search);
                })
            )
            ->when(isset($filters['category']), fn ($query) => $query->where('category', $filters['category']))
            ->when(isset($filters['region']), fn ($query) => $query->where('region', $filters['region']))
            ->when(
                isset($filters['year']),
                fn ($query) => $query->whereYear('published_at', $filters['year'])
            )
            ->orderByDesc('published_at')
            ->paginate(9)
            ->withQueryString();

        $categories = ProgramNews::CATEGORIES;
        $regions = ProgramNews::REGIONS;

        return view('content.pages.program-news.index', compact('newsItems', 'years', 'filters', 'categories', 'regions'));
    }

    public function show(ProgramNews $news)
    {
        abort_unless($news->is_published && $news->published_at->lte(now()), 404);

        $relatedNews = ProgramNews::query()
            ->published()
            ->whereKeyNot($news->getKey())
            ->where(function ($query) use ($news) {
                if ($news->category) {
                    $query->where('category', $news->category);
                }

                if ($news->region) {
                    if ($news->category) {
                        $query->orWhere('region', $news->region);
                    } else {
                        $query->where('region', $news->region);
                    }
                }
            })
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('content.pages.program-news.show', compact('news', 'relatedNews'));
    }
}
