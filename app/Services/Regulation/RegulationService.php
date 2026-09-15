<?php

namespace App\Services\Regulation;

use App\Http\Requests\Admin\Regulation\RegulationRequest;
use App\Models\Media;
use App\Models\Regulation;
use App\Models\RegulationType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegulationService
{
    public static function usable(?Media $media): bool
    {
        return $media
            && $media->mime_type === 'application/pdf'
            && (
                $media->is_public
                || (
                    $media->disk === 'local'
                    && str_starts_with($media->path, 'regulations/')
                )
            );
    }

    public function save(
        RegulationRequest $request,
        Regulation $regulation
    ): Regulation {
        return DB::transaction(function () use ($request, $regulation) {
            if ($regulation->exists) {
                $regulation = Regulation::query()
                    ->lockForUpdate()
                    ->findOrFail($regulation->id);
            }

            $data = $request->safe()->except([
                'main_media_id', 'remove_main',
                'main_file', 'attachments',
                'attachment_media_ids', 'documents',
            ]);

            $type = RegulationType::query()
                ->lockForUpdate()
                ->findOrFail($data['regulation_type_id']);

            if (
                !$type->is_active
                && (int) $regulation->regulation_type_id !== $type->id
            ) {
                throw ValidationException::withMessages([
                    'regulation_type_id' => 'Jenis regulasi tidak aktif.',
                ]);
            }

            if (
                $request->boolean('remove_main')
                && $request->filled('main_media_id')
            ) {
                throw ValidationException::withMessages([
                    'main_media_id' => 'Pilihan naskah tidak konsisten. Pilih ulang PDF.',
                ]);
            }

            $media = null;

            if ($request->filled('main_media_id')) {
                $media = Media::query()->lockForUpdate()
                    ->find($request->integer('main_media_id'));
            } elseif (!$request->boolean('remove_main') && $regulation->exists) {
                $current = $regulation->documents()
                    ->where('document_role', 'main')->first();

                if ($current) {
                    $media = Media::query()->lockForUpdate()
                        ->find($current->media_id);
                }
            }

            if (
                $request->filled('main_media_id')
                && !self::usable($media)
            ) {
                throw ValidationException::withMessages([
                    'main_media_id' => 'Pilih satu naskah PDF yang tersedia.',
                ]);
            }

            if ($media && (
                !self::usable($media)
                || !Storage::disk($media->disk)->exists($media->path)
            )) {
                throw ValidationException::withMessages([
                    'main_media_id' => 'File PDF tidak tersedia. Pilih atau unggah ulang.',
                ]);
            }

            if ($data['publication_status'] === 'published' && !$media) {
                throw ValidationException::withMessages([
                    'main_media_id' => 'Naskah PDF wajib dipilih sebelum diterbitkan.',
                ]);
            }

            if (!$regulation->exists) {
                $data['slug'] = (
                    Str::slug(Str::limit($data['title'], 100, ''))
                    ?: 'regulasi'
                ) . '-' . Str::lower(Str::random(12));

                $data['created_by'] = $request->user()->id;
            }

            $data['updated_by'] = $request->user()->id;

            if (
                $data['publication_status'] === 'published'
                && empty($data['published_at'])
            ) {
                $data['published_at'] = now();
            }

            $regulation->fill($data)->save();

            /*
             * Remove old document associations, not media files.
             * Keep an existing main association when the selected PDF is unchanged.
             */
            $keep = $media
                ? $regulation->documents()
                    ->where('document_role', 'main')
                    ->where('media_id', $media->id)
                    ->first()
                : null;

            $delete = $regulation->documents();

            if ($keep) {
                $delete->where('id', '!=', $keep->id);
            }

            $delete->delete();

            if ($keep) {
                $keep->update(['label' => 'Naskah regulasi', 'sort_order' => 0]);
            } elseif ($media) {
                $regulation->documents()->create([
                    'media_id' => $media->id,
                    'label' => 'Naskah regulasi',
                    'document_role' => 'main',
                    'sort_order' => 0,
                ]);
            }

            return $regulation;
        });
    }
}
