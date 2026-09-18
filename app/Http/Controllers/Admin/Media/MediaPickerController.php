<?php

namespace App\Http\Controllers\Admin\Media;

use App\Http\Controllers\Controller;
use App\Services\Access\UnitAccessService;
use App\Http\Requests\Admin\Media\StoreMediaRequest;
use App\Models\Media;
use App\Models\Unit;
use App\Services\Media\MediaStorageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaPickerController extends Controller
{
    private const TYPES = [
        'all',
        'image',
        'document',
        'audio',
        'video',
        'other',
    ];

    public function index(
        Request $request
    ): JsonResponse {
        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $unitId = (string) $request->input(
            'unit_id',
            ''
        );

        $type = (string) $request->input(
            'type',
            'all'
        );

        $mediaId = (int) $request->input(
            'id',
            0
        );

        if (
            ! in_array(
                $type,
                self::TYPES,
                true
            )
        ) {
            $type = 'all';
        }

        $query = Media::query()
            ->with('unit')
            ->where(
                'is_public',
                true
            );

        if ($type !== 'all') {
            $query->where(
                'type',
                $type
            );
        }

        if ($mediaId > 0) {
            $query->whereKey(
                $mediaId
            );
        } else {
            $query
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
                                        'original_name',
                                        'ilike',
                                        '%' . $search . '%'
                                    )
                                    ->orWhere(
                                        'file_name',
                                        'ilike',
                                        '%' . $search . '%'
                                    )
                                    ->orWhere(
                                        'alt_text',
                                        'ilike',
                                        '%' . $search . '%'
                                    );
                            }
                        );
                    }
                )
                ->when(
                    $unitId === 'global',
                    fn ($query) =>
                        $query->whereNull(
                            'unit_id'
                        )
                )
                ->when(
                    $unitId !== ''
                    && $unitId !== 'global',
                    fn ($query) =>
                        $query->where(
                            'unit_id',
                            $unitId
                        )
                );
        }

        $media = $query
            ->latest()
            ->paginate(24);

        $units = app(UnitAccessService::class)
            ->forCurrentRoute(request()->user(), 'media')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        return response()->json([
            'data' =>
                $media
                    ->getCollection()
                    ->map(
                        fn (Media $item) =>
                            $this->serialize(
                                $item
                            )
                    )
                    ->values(),

            'meta' => [
                'current_page' =>
                    $media->currentPage(),

                'last_page' =>
                    $media->lastPage(),

                'has_more' =>
                    $media->hasMorePages(),
            ],

            'filters' => [
                'units' => $units,
            ],
        ]);
    }

    public function store(
        StoreMediaRequest $request,
        MediaStorageService $storage
    ): JsonResponse {
        $expectedType = (string) $request->input(
            'expected_type',
            'all'
        );

        if (
            ! in_array(
                $expectedType,
                self::TYPES,
                true
            )
        ) {
            $expectedType = 'all';
        }

        $file = $request->file('file');

        $detectedType =
            $storage->detectType(
                $file
            );

        if (
            $expectedType !== 'all'
            && $detectedType !== $expectedType
        ) {
            return response()->json([
                'message' =>
                    'Jenis file tidak sesuai dengan kebutuhan media.',

                'errors' => [
                    'file' => [
                        'File harus bertipe '
                        . $expectedType
                        . '.',
                    ],
                ],
            ], 422);
        }

        $media = $storage->create(
            $file,
            [
                'unit_id' =>
                    $request->input(
                        'unit_id'
                    ),

                'title' =>
                    $request->input(
                        'title'
                    ),

                'alt_text' =>
                    $request->input(
                        'alt_text'
                    ),

                'description' =>
                    $request->input(
                        'description'
                    ),

                'is_public' =>
                    true,
            ]
        );

        $media->load('unit');

        return response()->json([
            'message' =>
                'Media berhasil diunggah.',

            'data' =>
                $this->serialize(
                    $media
                ),
        ], 201);
    }

    private function serialize(
        Media $media
    ): array {
        return [
            'id' =>
                $media->id,

            'title' =>
                $media->title
                ?: $media->original_name,

            'original_name' =>
                $media->original_name,

            'alt_text' =>
                $media->alt_text
                ?: '',

            'url' =>
                Storage::disk(
                    $media->disk
                )->url(
                    $media->path
                ),

            'type' =>
                $media->type,

            'mime_type' =>
                $media->mime_type,

            'extension' =>
                $media->extension,

            'size' =>
                $media->size,

            'unit_id' =>
                $media->unit_id,

            'unit_name' =>
                $media->unit?->name
                ?: 'Global',

            'unit' =>
                $media->unit?->name
                ?: 'Global',
        ];
    }
}
