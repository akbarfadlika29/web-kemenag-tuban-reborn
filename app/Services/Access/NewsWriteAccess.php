<?php

namespace App\Services\Access;

use App\Models\News;
use App\Models\User;

class NewsWriteAccess
{
    public function __construct(
        private readonly AccessService $access
    ) {
    }

    public function check(
        string $action,
        ?News $record,
        array $data
    ): void {
        abort_unless(
            in_array($action, ['create', 'update', 'delete'], true),
            403
        );

        $actorId = request()->user()?->id;
        abort_unless($actorId !== null, 401);

        $actor = User::findOrFail($actorId);

        abort_unless($actor->is_active, 403, 'Akun tidak aktif.');

        $permission = 'news.'.$action;

        if ($action !== 'create') {
            abort_unless($record !== null, 404);

            $this->requireUnit(
                $actor,
                $permission,
                $record->unit_id
            );
        }

        if ($action !== 'delete') {
            $destination = $data['unit_id'] ?? $record?->unit_id;

            abort_unless(
                is_int($destination) || is_string($destination),
                403,
                'Unit berita tidak valid.'
            );

            $this->requireUnit($actor, $permission, $destination);
        }

        $wasPublished = $record?->status === 'published';

        if ($action === 'delete') {
            if ($wasPublished) {
                $this->requireUnit(
                    $actor,
                    'news.unpublish',
                    $record->unit_id
                );
            }

            return;
        }

        $nextStatus = $data['status'] ?? $record?->status;

        if ($nextStatus === 'published') {
            // Periksa unit tujuan publikasi.
            $this->requireUnit(
                $actor,
                'news.publish',
                $destination
            );

            if ($wasPublished) {
                // Memindahkan berita terbit juga mengubah data unit asal.
                $this->requireUnit(
                    $actor,
                    'news.publish',
                    $record->unit_id
                );
            }
        } elseif ($wasPublished) {
            $this->requireUnit(
                $actor,
                'news.unpublish',
                $record->unit_id
            );
        }
    }

    private function requireUnit(
        User $actor,
        string $permission,
        int|string|null $unitId
    ): void {
        abort_unless(
            $this->access->allowsUnit($actor, $permission, $unitId),
            403,
            'Izin atau cakupan unit tidak mencukupi untuk tindakan berita ini.'
        );
    }
}