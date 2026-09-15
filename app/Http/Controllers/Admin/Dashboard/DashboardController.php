<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Announcement;
use App\Models\Gallery;
use App\Models\Media;
use App\Models\News;
use App\Models\Page;
use App\Models\PpidInformation;
use App\Models\Service;
use App\Models\Unit;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'news' => News::query()->count(),
            'announcements' => Announcement::query()->count(),
            'agendas' => Agenda::query()->count(),
            'galleries' => Gallery::query()->count(),
            'ppid' => PpidInformation::query()->count(),
            'services' => Service::query()->count(),
            'pages' => Page::query()->count(),
            'media' => Media::query()->count(),
            'units' => \App\Support\ManagedUsers::visibleUnitCount(auth()->user()),
            'users' => \App\Support\ManagedUsers::query(auth()->user(), 'users.view')->count(),
            'active_users' => \App\Support\ManagedUsers::query(auth()->user(), 'users.view')
                ->where('is_active', true)
                ->count(),
        ];

        $latestNews = News::query()
            ->with([
                'category',
                'unit',
            ])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $latestAnnouncements = Announcement::query()
            ->with('unit')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $upcomingAgendas = Agenda::query()
            ->with('unit')
            ->where(
                'start_at',
                '>=',
                now()
            )
            ->orderBy('start_at')
            ->limit(5)
            ->get();

        return view(
            'admin.dashboard.index',
            compact(
                'stats',
                'latestNews',
                'latestAnnouncements',
                'upcomingAgendas'
            )
        );
    }
}