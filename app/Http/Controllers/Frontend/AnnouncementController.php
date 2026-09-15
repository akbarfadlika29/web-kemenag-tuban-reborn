<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $announcements = Announcement::query()
            ->published()
            ->with([
                'unit',
                'coverMedia',
            ])
            ->when(
                $search !== '',
                fn ($query) =>
                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'title',
                                    'ilike',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'excerpt',
                                    'ilike',
                                    '%' . $search . '%'
                                );
                        }
                    )
            )
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(15)
            ->withQueryString();

        return view(
            'frontend.announcements.index',
            compact(
                'announcements',
                'search'
            )
        );
    }

    public function show(string $slug): View
    {
        $announcement = Announcement::query()
            ->published()
            ->with([
                'unit',
                'coverMedia',
                'attachmentMedia',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view(
            'frontend.announcements.show',
            compact('announcement')
        );
    }
}