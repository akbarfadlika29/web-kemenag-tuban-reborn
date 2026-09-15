<?php

namespace App\Http\Controllers\Admin\Frontend;

use App\Http\Controllers\Controller;
use App\Services\Frontend\ExperienceSettings;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class ExperienceController extends Controller
{
    public function edit(ExperienceSettings $settings)
    {
        return view('admin.frontend.settings', [
            'values' => $settings->all(),
        ]);
    }

    public function update(Request $request, ExperienceSettings $settings)
    {
        $rules = [
            'palette' => ['required', 'in:emerald,teal,slate'],
            'width' => ['required', 'integer', 'in:1100,1180,1280'],
            'font_size' => ['required', 'integer', 'in:15,16,17'],
        ];

        foreach (ExperienceSettings::DEFAULTS as $key => $default) {
            if (is_bool($default)) $rules[$key] = ['required', 'boolean'];
        }

        $data = $request->validate($rules);

        foreach (ExperienceSettings::DEFAULTS as $key => $default) {
            if (is_bool($default)) $data[$key] = $request->boolean($key);
        }

        $settings->save($data);

        return back()->with('success', 'Pengaturan frontend berhasil disimpan.');
    }

    public function statistics(Request $request)
    {
        // RBAC: global statistics scope
        $actor = $request->user();

        abort_unless(
            $actor
            && app(\App\Services\Access\AccessService::class)->scope(
                $actor,
                'content-statistics.view'
            ) === 'all_units',
            403,
            'Statistik global memerlukan akses seluruh unit.'
        );

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'sort' => ['nullable', 'in:views,likes'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim($filters['search'] ?? '');
        $sort = $filters['sort'] ?? 'views';

        $counts = DB::table('public_page_interactions')
            ->select('page_key')
            ->selectRaw("SUM(CASE WHEN kind = 'view' THEN 1 ELSE 0 END) AS views")
            ->selectRaw("SUM(CASE WHEN kind = 'like' THEN 1 ELSE 0 END) AS likes")
            ->groupBy('page_key')
            ->get()
            ->keyBy('page_key');

        $totals = [
            'views' => (int) $counts->sum('views'),
            'likes' => (int) $counts->sum('likes'),
            'pages' => $counts->count(),
            'visitors' => DB::table('public_page_interactions')
                ->where('kind', 'view')
                ->distinct()
                ->count('visitor_key'),

            'visitors_today' => DB::table('public_page_interactions')
                ->where('kind', 'view')
                ->where('period', now()->toDateString())
                ->distinct()
                ->count('visitor_key'),
        ];

        $map = [
            'Page' => ['Halaman', 'admin.pages.edit'],
            'News' => ['Berita', 'admin.news.edit'],
            'Announcement' => ['Pengumuman', 'admin.announcements.edit'],
            'Agenda' => ['Agenda', 'admin.agendas.edit'],
            'Gallery' => ['Galeri', 'admin.galleries.edit'],
            'PpidInformation' => ['Informasi PPID', 'admin.ppid-informations.edit'],
            'Service' => ['Layanan', 'admin.services.edit'],
            'Regulation' => ['Regulasi', 'admin.regulations.edit'],
        ];

        $rows = collect();

        if ($counts->isNotEmpty()) {
            foreach ($map as $model => [$label, $editRoute]) {
                $class = 'App\\Models\\'.$model;
                if (!class_exists($class)) continue;

                $class::query()->chunkById(300, function ($models) use (
                    $counts, $rows, $label, $editRoute, $search
                ) {
                    foreach ($models as $model) {
                        $key = hash('sha256', get_class($model).':'.$model->getKey());
                        $count = $counts->get($key);
                        if (!$count) continue;

                        $title = $model->getAttribute('title')
                            ?: $model->getAttribute('name')
                            ?: 'Tanpa judul';

                        if ($search !== '' &&
                            mb_stripos($title.' '.$label, $search) === false) {
                            continue;
                        }

                        $rows->push([
                            'title' => $title,
                            'type' => $label,
                            'views' => (int) $count->views,
                            'likes' => (int) $count->likes,
                            'updated' => $model->updated_at?->format('d/m/Y'),
                            'edit' => Route::has($editRoute)
                                ? route($editRoute, $model->getKey())
                                : null,
                        ]);
                    }
                });
            }
        }

        $rows = $rows->sortByDesc($sort)->values();
        $page = max(1, (int) ($filters['page'] ?? 1));

        $items = new LengthAwarePaginator(
            $rows->forPage($page, 20)->values(),
            $rows->count(),
            20,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.frontend.statistics', compact(
            'items', 'totals', 'search', 'sort'
        ));
    }
}
