<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(): View
    {
        $galleries = Gallery::query()
            ->published()
            ->with([
                'unit',
                'coverMedia',
            ])
            ->withCount('items')
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(12);

        return view(
            'frontend.galleries.index',
            compact('galleries')
        );
    }

    public function show(string $slug): View
    {
        $gallery = Gallery::query()
            ->published()
            ->with([
                'unit',
                'coverMedia',
                'items.media',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view(
            'frontend.galleries.show',
            compact('gallery')
        );
    }
}