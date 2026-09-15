<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\HeroSlide;
use App\Models\News;
use App\Models\PpidInformation;
use App\Models\Service;
use App\Models\Unit;
use App\Services\QuickLink\QuickLinkFrontendService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(
        QuickLinkFrontendService $quickLinkService
    ): View {
        $quickLinks =
            $quickLinkService->getActive();

        $heroSlides = HeroSlide::query()
            ->active()
            ->with('media')
            ->whereHas(
                'media',
                fn ($query) =>
                    $query->where(
                        'type',
                        'image'
                    )
            )
            ->ordered()
            ->get();

        $latestNews = News::query()
            ->published()
            ->with([
                'category',
                'unit',
                'coverMedia',
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        $newsUnits = Unit::query()
            ->whereHas(
                'news',
                function ($query) {
                    $query->published();
                }
            )
            ->select(
                'id',
                'name',
                'slug'
            )
            ->get();

        $announcements =
            Announcement::query()
                ->published()
                ->with([
                    'unit',
                    'coverMedia',
                ])
                ->orderByDesc('is_pinned')
                ->orderByDesc('published_at')
                ->limit(5)
                ->get();

        $agendas = Agenda::query()
            ->published()
            ->with([
                'unit',
                'coverMedia',
            ])
            ->where(
                'start_at',
                '>=',
                now()->startOfDay()
            )
            ->orderBy('start_at')
            ->limit(4)
            ->get();

        $services = Service::query()
            ->published()
            ->with([
                'category',
                'unit',
                'coverMedia',
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        $ppidInformations =
            PpidInformation::query()
                ->published()
                ->with([
                    'category',
                    'unit',
                    'primaryDocument.media',
                ])
                ->orderByDesc('is_featured')
                ->orderByDesc('published_at')
                ->limit(6)
                ->get();

        return view(
            'frontend.home.index',
            compact(
                'quickLinks',
                'heroSlides',
                'latestNews',
                'newsUnits',
                'announcements',
                'agendas',
                'services',
                'ppidInformations'
            )
        );
    }
}
