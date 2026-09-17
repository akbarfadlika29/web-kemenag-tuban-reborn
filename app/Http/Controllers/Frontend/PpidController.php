<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PpidInformation;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PpidController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'classification' => [
                'nullable',
                'in:berkala,serta_merta,setiap_saat,dikecualikan',
            ],
            'search' => ['nullable', 'string', 'max:200'],
            'year' => ['nullable', 'integer', 'between:1,9999'],
            'unit_id' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'in:20,50,100,all'],
        ]);

        $classification = $filters['classification'] ?? '';
        $search = trim($filters['search'] ?? '');
        $year = $filters['year'] ?? '';
        $unitId = $filters['unit_id'] ?? '';
        $perPage = (string) ($filters['per_page'] ?? '20');

        $classifications = [
            '' => 'Daftar Informasi Publik',
            'berkala' => 'Informasi Berkala',
            'serta_merta' => 'Informasi Serta-merta',
            'setiap_saat' => 'Informasi Tersedia Setiap Saat',
            'dikecualikan' => 'Informasi Dikecualikan',
        ];

        $pageTitle = $classifications[$classification];

        $base = PpidInformation::query()
            ->published()
            ->where('access_level', 'public')
            ->with('unit');

        if ($classification !== 'dikecualikan') {
            $base->where('classification', '!=', 'dikecualikan');
        }

        $years = (clone $base)
            ->whereNotNull('year')
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $units = Unit::query()
            ->whereIn(
                'id',
                (clone $base)
                    ->select('unit_id')
                    ->whereNotNull('unit_id')
            )
            ->orderBy('name')
            ->get(['id', 'name']);

        $query = (clone $base)
            ->when(
                $classification !== '',
                fn ($q) => $q->where('classification', $classification)
            )
            ->when(
                $year !== '',
                fn ($q) => $q->where('year', $year)
            )
            ->when(
                $unitId !== '',
                fn ($q) => $q->where('unit_id', $unitId)
            )
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('title', 'ilike', '%'.$search.'%')
                        ->orWhere(
                            'information_holder',
                            'ilike',
                            '%'.$search.'%'
                        )
                        ->orWhere(
                            'person_in_charge',
                            'ilike',
                            '%'.$search.'%'
                        );
                });
            });

        $limit = $perPage === 'all'
            ? max(1, (clone $query)->count())
            : (int) $perPage;

        $informations = $query
            ->with([
                'documents' => function ($q) {
                    $q->where('document_status', 'active')
                        ->whereHas('media', function ($q) {
                            $q->where('is_public', true)
                                ->where('disk', 'public');
                        })
                        ->with('media');
                },
            ])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(
                $limit,
                ['*'],
                'page',
                $perPage === 'all' ? 1 : null
            )
            ->withQueryString();

        return view('frontend.ppid.index', compact(
            'informations',
            'classification',
            'classifications',
            'pageTitle',
            'search',
            'year',
            'unitId',
            'perPage',
            'years',
            'units'
        ));
    }

    /*
     * Tautan detail lama tetap dapat digunakan.
     * Pengunjung diarahkan ke daftar klasifikasi terkait.
     */
    public function show(string $slug): RedirectResponse
    {
        $information = PpidInformation::query()
            ->published()
            ->where('access_level', 'public')
            ->where('slug', $slug)
            ->firstOrFail();

        return redirect()->route('ppid.index', [
            'classification' => $information->classification,
            'search' => mb_substr($information->title, 0, 200),
        ]);
    }
}