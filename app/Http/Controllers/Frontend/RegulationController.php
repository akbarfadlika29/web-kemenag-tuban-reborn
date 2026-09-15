<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Regulation;
use App\Models\RegulationType;
use App\Services\Regulation\RegulationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RegulationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:200',
            'type' => 'nullable|string|max:180',
            'year' => 'nullable|integer|between:1900,2200',
        ]);

        $type = !empty($filters['type'])
            ? RegulationType::where('slug', $filters['type'])->firstOrFail()
            : null;

        $query = Regulation::published()->with('type')->latest('published_at');

        if ($type) {
            $query->where('regulation_type_id', $type->id);
        }

        if (!empty($filters['year'])) {
            $query->where('year', $filters['year']);
        }

        if ($search = trim($filters['search'] ?? '')) {
            $query->where(fn ($q) =>
                $q->where('title', 'ilike', "%{$search}%")
                    ->orWhere('number', 'ilike', "%{$search}%")
            );
        }

        return view('frontend.regulations.index', [
            'items' => $query->paginate(12)->withQueryString(),
            'selectedType' => $type,
            'types' => RegulationType::whereHas(
                'regulations',
                fn ($q) => $q->published()
            )->orderBy('name')->get(),
        ]);
    }

    public function show(Regulation $regulation)
    {
        abort_unless(
            Regulation::published()->whereKey($regulation->id)->exists(),
            404
        );

        return view('frontend.regulations.show', [
            'regulation' => $regulation->load(
                'type', 'unit', 'documents.media'
            ),
        ]);
    }

    public function document(
        Request $request,
        Regulation $regulation,
        int $document
    ) {
        if (!$request->routeIs('admin.*')) {
            abort_unless(
                Regulation::published()->whereKey($regulation->id)->exists(),
                404
            );
        }

        $document = $regulation->documents()->where('document_role', 'main')
            ->with('media')
            ->findOrFail($document);

        abort_unless(RegulationService::usable($document->media), 404);

        $media = $document->media;
        $disk = Storage::disk($media->disk);

        abort_unless($disk->exists($media->path), 404);

        $headers = [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];

        $filename = 'regulasi-' . $regulation->id
            . '-' . $document->id . '.pdf';

        return $request->boolean('download')
            ? $disk->download($media->path, $filename, $headers)
            : $disk->response($media->path, $filename, $headers);
    }
}
