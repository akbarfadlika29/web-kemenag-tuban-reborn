<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\HeroSlide;
use App\Models\News;
use App\Models\PpidInformation;
use App\Models\Service;
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
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();

        $announcements =
            Announcement::query()
                ->published()
                ->orderByDesc('is_pinned')
                ->orderByDesc('published_at')
                ->limit(5)
                ->get();

        $agendas = Agenda::query()
            ->published()
            ->orderByDesc('start_at')
            ->limit(5)
            ->get();

        $services = Service::query()
            ->published()
            ->with('category')
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->limit(6)
            ->get();

        $ppidInformations =
            PpidInformation::query()
                ->published()
                ->where('access_level', 'public')
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
                'announcements',
                'agendas',
                'services',
                'ppidInformations'
            )
        );
    }
}
