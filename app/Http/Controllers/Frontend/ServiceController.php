<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $categoryId = (string) $request->input(
            'category_id',
            ''
        );

        $channel = (string) $request->input(
            'channel',
            ''
        );

        $categories = ServiceCategory::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $services = Service::query()
            ->published()
            ->with([
                'category',
                'unit',
                'coverMedia',
            ])
            ->when(
                $search !== '',
                function ($query) use ($search) {
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
                    );
                }
            )
            ->when(
                $categoryId !== '',
                fn ($query) =>
                    $query->where(
                        'category_id',
                        $categoryId
                    )
            )
            ->when(
                $channel !== '',
                fn ($query) =>
                    $query->where(
                        'service_channel',
                        $channel
                    )
            )
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->paginate(12)
            ->withQueryString();

        return view(
            'frontend.services.index',
            compact(
                'services',
                'categories',
                'search',
                'categoryId',
                'channel'
            )
        );
    }

    public function show(string $slug): View
    {
        $service = Service::query()
            ->published()
            ->with([
                'category',
                'unit',
                'coverMedia',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view(
            'frontend.services.show',
            compact('service')
        );
    }
}