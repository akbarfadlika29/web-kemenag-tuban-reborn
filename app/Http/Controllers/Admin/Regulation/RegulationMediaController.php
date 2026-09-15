<?php

namespace App\Http\Controllers\Admin\Regulation;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Unit;
use App\Services\Regulation\RegulationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RegulationMediaController extends Controller
{
    private function query()
    {
        return Media::query()
            ->where('mime_type', 'application/pdf')
            ->where(function ($query) {
                $query->where('is_public', true)
                    ->orWhere(function ($query) {
                        $query->where('disk', 'local')
                            ->where('path', 'like', 'regulations/%');
                    });
            });
    }

    private function serialize(Media $media): array
    {
        return [
            'id' => $media->id,
            'title' => $media->title ?: $media->original_name,
            'original_name' => $media->original_name,
            'alt_text' => '',
            'url' => route('admin.regulations.media.preview', $media->id),
            'type' => 'document',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => $media->size,
            'unit_id' => $media->unit_id,
            'unit_name' => $media->unit?->name ?: 'Global',
            'unit' => $media->unit?->name ?: 'Global',
        ];
    }

    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'unit_id' => ['nullable', 'string', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim((string) $request->input('search', ''));
        $unit = (string) $request->input('unit_id', '');

        $query = $this->query()->with('unit');

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'ilike', '%' . $search . '%')
                    ->orWhere('original_name', 'ilike', '%' . $search . '%');
            });
        }

        if ($unit === 'global') {
            $query->whereNull('unit_id');
        } elseif ($unit !== '') {
            if (!ctype_digit($unit)) {
                abort(422, 'Unit tidak valid.');
            }

            $query->where('unit_id', (int) $unit);
        }

        $media = $query->latest()->paginate(24);

        return response()->json([
            'data' => $media->getCollection()
                ->map(fn (Media $item) => $this->serialize($item))
                ->values(),
            'meta' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'has_more' => $media->hasMorePages(),
                'total' => $media->total(),
            ],
            'filters' => [
                'units' => Unit::where('is_active', true)
                    ->orderBy('name')->get(['id', 'name']),
            ],
        ]);
    }

    public function upload(Request $request)
    {
        $data = $request->validate([
            'file' => [
                'required', 'file', 'mimes:pdf',
                'mimetypes:application/pdf', 'max:20480',
            ],
            'unit_id' => ['nullable', 'integer', 'exists:units,id'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');

        $path = Storage::disk('local')->putFileAs(
            'regulations',
            $file,
            Str::uuid() . '.pdf'
        );

        if (!$path) {
            abort(500, 'PDF gagal disimpan.');
        }

        try {
            $media = Media::create([
                'unit_id' => $data['unit_id'] ?? null,
                'original_name' => $file->getClientOriginalName(),
                'file_name' => basename($path),
                'path' => $path,
                'disk' => 'local',
                'mime_type' => 'application/pdf',
                'extension' => 'pdf',
                'size' => $file->getSize(),
                'type' => 'document',
                'title' => ($data['title'] ?? null) ?: Str::limit(
                    pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    250,
                    ''
                ),
                'is_public' => false,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        $media->load('unit');

        return response()->json([
            'message' => 'PDF berhasil diunggah.',
            'data' => $this->serialize($media),
        ], 201);
    }

    public function preview(int $media)
    {
        $media = $this->query()->findOrFail($media);

        abort_unless(RegulationService::usable($media), 404);

        $disk = Storage::disk($media->disk);
        abort_unless($disk->exists($media->path), 404);

        return $disk->response(
            $media->path,
            'naskah-' . $media->id . '.pdf',
            [
                'Content-Type' => 'application/pdf',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}
