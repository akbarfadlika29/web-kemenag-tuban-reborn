<?php

namespace App\Http\Controllers\Admin\HeroSlide;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\HeroSlide\StoreHeroSlideRequest;
use App\Http\Requests\Admin\HeroSlide\UpdateHeroSlideRequest;
use App\Models\HeroSlide;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HeroSlideController extends Controller
{
    public function index(): View
    {
        $heroSlides = HeroSlide::query()
            ->with('media')
            ->ordered()
            ->paginate(20);

        return view(
            'admin.hero-slides.index',
            compact('heroSlides')
        );
    }

    public function create(): View
    {
        $nextSortOrder =
            $this->nextSortOrder();

        return view(
            'admin.hero-slides.create',
            compact('nextSortOrder')
        );
    }

    public function store(
        StoreHeroSlideRequest $request
    ): RedirectResponse {
        HeroSlide::create(
            $request->validated()
        );

        return redirect()
            ->route(
                'admin.hero-slides.index'
            )
            ->with(
                'success',
                'Slide hero berhasil ditambahkan.'
            );
    }

    public function edit(
        HeroSlide $heroSlide
    ): View {
        $heroSlide->load('media');

        return view(
            'admin.hero-slides.edit',
            compact('heroSlide')
        );
    }

    public function update(
        UpdateHeroSlideRequest $request,
        HeroSlide $heroSlide
    ): RedirectResponse {
        $heroSlide->update(
            $request->validated()
        );

        return redirect()
            ->route(
                'admin.hero-slides.index'
            )
            ->with(
                'success',
                'Slide hero berhasil diperbarui.'
            );
    }

    public function destroy(
        HeroSlide $heroSlide
    ): RedirectResponse {
        $heroSlide->delete();

        return redirect()
            ->route(
                'admin.hero-slides.index'
            )
            ->with(
                'success',
                'Slide hero berhasil dihapus.'
            );
    }

    private function nextSortOrder(): int
    {
        return (
            (int) HeroSlide::query()
                ->max('sort_order')
        ) + 1;
    }
}
