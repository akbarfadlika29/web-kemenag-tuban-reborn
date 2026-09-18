<?php

namespace App\Http\Controllers\Admin\Media;

use App\Http\Controllers\Controller;
use App\Services\Access\UnitAccessService;
use App\Http\Requests\Admin\Media\StoreMediaRequest;
use App\Http\Requests\Admin\Media\UpdateMediaRequest;
use App\Models\Media;
use App\Models\Unit;
use App\Services\Media\MediaStorageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(
        Request $request
    ): View {
        $search = trim(
            (string) $request->input(
                'search',
                ''
            )
        );

        $type = $request->input(
            'type',
            ''
        );

        $unitId = $request->input(
            'unit_id',
            ''
        );

        $media = Media::query()
            ->with('unit')
            ->when(
                $search !== '',
                function ($query) use ($search) {
                    $query->where(
                        function ($query) use ($search) {
                            $query
                                ->where(
                                    'original_name',
                                    'ilike',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'title',
                                    'ilike',
                                    '%' . $search . '%'
                                )
                                ->orWhere(
                                    'file_name',
                                    'ilike',
                                    '%' . $search . '%'
                                );
                        }
                    );
                }
            )
            ->when(
                $type !== '',
                fn ($query) =>
                    $query->where(
                        'type',
                        $type
                    )
            )
            ->when(
                $unitId !== '',
                fn ($query) =>
                    $query->where(
                        'unit_id',
                        $unitId
                    )
            )
            ->latest()
            ->paginate(24)
            ->withQueryString();

        $units = app(UnitAccessService::class)
            ->forCurrentRoute(request()->user(), 'media')
            ->orderBy('name')
            ->get();

        return view(
            'admin.media.index',
            compact(
                'media',
                'units',
                'search',
                'type',
                'unitId'
            )
        );
    }

    public function create(): View
    {
        $units = app(UnitAccessService::class)
            ->forCurrentRoute(request()->user(), 'media')
            ->orderBy('name')
            ->get();

        return view(
            'admin.media.create',
            compact('units')
        );
    }

    public function store(
        StoreMediaRequest $request,
        MediaStorageService $storage
    ): RedirectResponse {
        $storage->create(
            $request->file('file'),
            [
                'unit_id' =>
                    $request->input('unit_id'),

                'title' =>
                    $request->input('title'),

                'alt_text' =>
                    $request->input('alt_text'),

                'description' =>
                    $request->input('description'),

                'is_public' =>
                    $request->boolean('is_public'),
            ]
        );

        return redirect()
            ->route('admin.media.index')
            ->with(
                'success',
                'Media berhasil diunggah.'
            );
    }

    public function edit(
        Media $medium
    ): View {
        $units = app(UnitAccessService::class)
            ->forCurrentRoute(request()->user(), 'media')
            ->orderBy('name')
            ->get();

        return view(
            'admin.media.edit',
            compact(
                'medium',
                'units'
            )
        );
    }

    public function update(
        UpdateMediaRequest $request,
        Media $medium,
        MediaStorageService $storage
    ): RedirectResponse {
        // regulation-media-update-guard
        if (
            $medium->isUsedByRegulations()
            && (
                $request->hasFile('file')
                || $medium->is_public !== $request->boolean('is_public')
            )
        ) {
            return back()->with(
                'error',
                'File digunakan oleh regulasi. Ganti dokumen melalui formulir Regulasi; jangan menimpa file atau mengubah aksesnya di sini.'
            );
        }

        $data = $request->validated();

        unset($data['file']);

        if ($request->hasFile('file')) {
            $storage->replace(
                $medium,
                $request->file('file'),
                $data
            );
        } else {
            $medium->update($data);
        }

        return redirect()
            ->route('admin.media.index')
            ->with(
                'success',
                'Media berhasil diperbarui.'
            );
    }

    public function destroy(
        Media $medium
    ): RedirectResponse {
        if ($medium->isInUse()) {
            return redirect()
                ->route('admin.media.index')
                ->with(
                    'error',
                    'Media tidak dapat dihapus karena sedang digunakan oleh modul lain.'
                );
        }

        if (
            Storage::disk(
                $medium->disk
            )->exists(
                $medium->path
            )
        ) {
            Storage::disk(
                $medium->disk
            )->delete(
                $medium->path
            );
        }

        $medium->delete();

        return redirect()
            ->route('admin.media.index')
            ->with(
                'success',
                'Media berhasil dihapus.'
            );
    }
}
