<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\PpidInformation;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PpidController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'classification' => [
                'nullable',
                'string',
                'in:berkala,serta_merta,setiap_saat',
            ],
            'search' => ['nullable', 'string', 'max:200'],
            'year' => ['nullable', 'integer', 'between:1,9999'],
            'unit_id' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $classification = $filters['classification'] ?? '';
        $search = trim($filters['search'] ?? '');
        $year = $filters['year'] ?? '';
        $unitId = $filters['unit_id'] ?? '';

        $classifications = [
            '' => 'Daftar Informasi Publik',
            'berkala' => 'Informasi Berkala',
            'serta_merta' => 'Informasi Serta-merta',
            'setiap_saat' => 'Informasi Setiap Saat',
        ];

        $pageTitle = $classifications[$classification];

        $base = PpidInformation::query()->published();

        $years = (clone $base)
            ->whereNotNull('year')
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $units = Unit::query()
            ->whereIn(
                'id',
                (clone $base)->select('unit_id')->whereNotNull('unit_id')
            )
            ->orderBy('name')
            ->get(['id', 'name']);

        $informations = (clone $base)
            ->with(['category', 'unit'])
            ->when(
                $classification !== '',
                fn ($query) => $query->where('classification', $classification)
            )
            ->when(
                $year !== '',
                fn ($query) => $query->where('year', $year)
            )
            ->when(
                $unitId !== '',
                fn ($query) => $query->where('unit_id', $unitId)
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'ilike', '%'.$search.'%')
                        ->orWhere('excerpt', 'ilike', '%'.$search.'%');
                });
            })
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('frontend.ppid.index', compact(
            'informations',
            'classification',
            'classifications',
            'pageTitle',
            'search',
            'year',
            'unitId',
            'years',
            'units',
        ));
    }

    public function show(string $slug): View
    {
        $information = PpidInformation::query()
            ->published()
            ->with([
                'category',
                'unit',
                'documents.media',
            ])
            ->where('slug', $slug)
            ->firstOrFail();

        return view('frontend.ppid.show', compact('information'));
    }
}
