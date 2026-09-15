<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(
        string $slug
    ): View {
        $page = Page::query()
            ->published()
            ->with([
                'unit',
                'coverMedia',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view(
            'frontend.pages.show',
            compact('page')
        );
    }
}
