<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\News;
use App\Models\PpidInformation;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $news = new Collection();
        $announcements = new Collection();
        $ppidInformations = new Collection();
        $services = new Collection();

        if ($search !== '') {
            $news = News::query()
                ->published()
                ->where(
                    'title',
                    'ILIKE',
                    '%' . $search . '%'
                )
                ->orderByDesc('published_at')
                ->limit(6)
                ->get();

            $announcements = Announcement::query()
                ->published()
                ->where(
                    'title',
                    'ILIKE',
                    '%' . $search . '%'
                )
                ->orderByDesc('published_at')
                ->limit(6)
                ->get();

            $ppidInformations = PpidInformation::query()
                ->published()
                ->where('access_level', 'public')
                ->where(
                    'title',
                    'ILIKE',
                    '%' . $search . '%'
                )
                ->orderByDesc('published_at')
                ->limit(6)
                ->get();

            $services = Service::query()
                ->published()
                ->where(
                    'title',
                    'ILIKE',
                    '%' . $search . '%'
                )
                ->orderByDesc('published_at')
                ->limit(6)
                ->get();
        }

        return view(
            'frontend.search.index',
            compact(
                'search',
                'news',
                'announcements',
                'ppidInformations',
                'services'
            )
        );
    }
}
