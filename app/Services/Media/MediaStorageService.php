<?php

namespace App\Services\Media;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class MediaStorageService
{
    public function create(
        UploadedFile $file,
        array $attributes = []
    ): Media {
        $stored = $this->storeFile($file);

        try {
            return Media::query()->create([
                'unit_id' =>
                    $attributes['unit_id'] ?? null,

                'original_name' =>
                    $file->getClientOriginalName(),

                'file_name' =>
                    $stored['file_name'],

                'path' =>
                    $stored['path'],

                'disk' =>
                    'public',

                'mime_type' =>
                    $stored['mime_type'],

                'extension' =>
                    $stored['extension'],

                'size' =>
                    $file->getSize(),

                'type' =>
                    $stored['type'],

                'title' =>
                    $attributes['title'] ?? null,

                'alt_text' =>
                    $attributes['alt_text'] ?? null,

                'description' =>
                    $attributes['description'] ?? null,

                'is_public' =>
                    $attributes['is_public'] ?? true,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(
                $stored['path']
            );

            throw $exception;
        }
    }

    public function replace(
        Media $media,
        UploadedFile $file,
        array $attributes = []
    ): Media {
        $stored = $this->storeFile($file);

        $oldDisk = $media->disk;
        $oldPath = $media->path;

        try {
            $media->update(
                array_merge(
                    $attributes,
                    [
                        'original_name' =>
                            $file->getClientOriginalName(),

                        'file_name' =>
                            $stored['file_name'],

                        'path' =>
                            $stored['path'],

                        'disk' =>
                            'public',

                        'mime_type' =>
                            $stored['mime_type'],

                        'extension' =>
                            $stored['extension'],

                        'size' =>
                            $file->getSize(),

                        'type' =>
                            $stored['type'],
                    ]
                )
            );
        } catch (Throwable $exception) {
            Storage::disk('public')->delete(
                $stored['path']
            );

            throw $exception;
        }

        if (
            $oldPath
            && Storage::disk($oldDisk)->exists($oldPath)
        ) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        return $media->refresh();
    }

    public function detectType(
        UploadedFile $file
    ): string {
        $mimeType = (string) $file->getMimeType();

        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        return match (true) {
            str_starts_with(
                $mimeType,
                'image/'
            ) => 'image',

            str_starts_with(
                $mimeType,
                'video/'
            ) => 'video',

            str_starts_with(
                $mimeType,
                'audio/'
            ) => 'audio',

            in_array(
                $extension,
                [
                    'pdf',
                    'doc',
                    'docx',
                    'xls',
                    'xlsx',
                    'ppt',
                    'pptx',
                    'txt',
                    'csv',
                ],
                true
            ) => 'document',

            default => 'other',
        };
    }

    private function storeFile(
        UploadedFile $file
    ): array {
        $extension = strtolower(
            $file->getClientOriginalExtension()
        );

        $fileName =
            Str::uuid()
            . ($extension ? '.' . $extension : '');

        $path = $file->storeAs(
            'media/' . now()->format('Y/m'),
            $fileName,
            'public'
        );

        return [
            'file_name' =>
                $fileName,

            'path' =>
                $path,

            'mime_type' =>
                $file->getMimeType(),

            'extension' =>
                $extension,

            'type' =>
                $this->detectType($file),
        ];
    }
}
