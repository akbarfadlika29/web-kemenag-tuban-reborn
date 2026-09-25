<?php

namespace App\Services\Access;

use App\Models\Media;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class NewsEditorialService
{
    public function __construct(
        private readonly NewsEditorialPolicy $policy,
        private readonly AccessService $access
    ) {}

    public function assertVersion(News $news, mixed $version): void
    {
        abort_unless(
            is_scalar($version)
            && ctype_digit((string) $version)
            && (string) $news->editorial_version === (string) $version,
            409,
            'Berita sudah berubah. Muat ulang halaman sebelum melanjutkan.'
        );
    }

    public function prepareWrite(
        User $actor,
        ?News $news,
        array $data
    ): array {
        unset($data['editorial_version']);

        if ($news) {
            abort_unless(
                $this->policy->canWrite($actor, $news),
                403,
                'Berita terkunci, bukan milik Anda, atau izin tidak mencukupi.'
            );

            $this->assertVersion(
                $news,
                request()->input('editorial_version')
            );

            if ($news->editorial_state === 'submitted') {
                abort_unless(
                    (string) ($data['unit_id'] ?? $news->unit_id)
                        === (string) $news->unit_id,
                    422,
                    'Unit berita yang sedang diajukan tidak dapat dipindahkan.'
                );

                abort_if(
                    ($data['status'] ?? 'draft') === 'archived',
                    422,
                    'Tolak pengajuan terlebih dahulu sebelum mengarsipkan.'
                );
            }
        }

        $destination = $data['unit_id'] ?? $news?->unit_id;

        $editorAtDestination =
            $this->access->allowsUnit($actor, 'news.review', $destination)
            || $this->access->allowsUnit($actor, 'news.publish', $destination);

        if (!$editorAtDestination) {
            abort_unless(
                ($data['status'] ?? 'draft') === 'draft',
                403,
                'Penulis menyimpan draf lalu mengajukannya untuk diperiksa.'
            );
        }

        $state = $news?->editorial_state ?? 'draft';

        if (($data['status'] ?? 'draft') === 'published') {
            $state = 'approved';
        } elseif ($state === 'approved') {
            $state = 'draft';
        }

        $data['editorial_state'] = $state;
        $data['editorial_version'] =
            (int) ($news?->editorial_version ?? 0) + 1;

        $changed = !$news;

        foreach ([
            'title', 'content', 'excerpt', 'category_id', 'cover_media_id',
        ] as $field) {
            if (
                $news
                && array_key_exists($field, $data)
                && (string) $data[$field]
                    !== (string) $news->getAttribute($field)
            ) {
                $changed = true;
            }
        }

        $data['revision_required'] = $changed
            ? false
            : (bool) ($news?->revision_required ?? false);

        if (
            ($data['status'] ?? 'draft') === 'published'
            && $data['revision_required']
        ) {
            throw ValidationException::withMessages([
                'status' => 'Perbaiki berita yang ditolak sebelum menerbitkan.',
            ]);
        }

        if ($state !== 'rejected') {
            $data['rejection_reason'] = null;
        }

        if (($data['status'] ?? 'draft') !== 'published') {
            $data['published_at'] = null;
        }

        return $data;
    }

    public function record(
        News $news,
        User $actor,
        string $action,
        ?string $reason = null
    ): void {
        DB::table('news_editorial_events')->insert([
            'news_id' => $news->id,
            'actor_id' => $actor->id,
            'action' => $action,
            'reason' => $reason,
            'version' => $news->editorial_version,
            'created_at' => now(),
        ]);
    }

    public function transition(
        User $actor,
        News $news,
        string $action,
        int $version,
        ?string $reason
    ): void {
        $this->assertVersion($news, $version);

        $allowed = match ($action) {
            'submit' => $this->policy->canSubmit($actor, $news),
            'reject' => $this->policy->canReject($actor, $news),
            'publish' => $this->policy->canPublish($actor, $news),
            default => false,
        };

        abort_unless(
            $allowed,
            403,
            'Tindakan tidak diizinkan untuk status, pemilik, atau unit berita ini.'
        );

        if ($action === 'reject' && trim((string) $reason) === '') {
            throw ValidationException::withMessages([
                'reason' => 'Alasan penolakan wajib diisi.',
            ]);
        }

        if (in_array($action, ['submit', 'publish'], true)) {
            if (
                trim(strip_tags($news->content)) === ''
                || trim($news->title) === ''
                || !$news->unit_id
                || !$news->category_id
                || !$news->cover_media_id
            ) {
                throw ValidationException::withMessages([
                    'news' => 'Lengkapi judul, isi, unit, kategori, dan gambar utama.',
                ]);
            }

            $media = Media::query()
                ->whereKey($news->cover_media_id)
                ->where('type', 'image')
                ->where('is_public', true)
                ->first();

            abort_unless(
                $media
                && $this->access->allowsUnit(
                    $actor, 'media.view', $media->unit_id
                ),
                403,
                'Gambar utama tidak tersedia atau berada di luar izin media Anda.'
            );
        }

        $news->editorial_state = match ($action) {
            'submit' => 'submitted',
            'reject' => 'rejected',
            'publish' => 'approved',
        };

        $news->revision_required = $action === 'reject';
        $news->rejection_reason = $action === 'reject'
            ? trim((string) $reason)
            : null;

        $news->status = $action === 'publish' ? 'published' : 'draft';
        $news->published_at = $action === 'publish' ? now() : null;
        $news->editorial_version = (int) $news->editorial_version + 1;
        $news->save();

        $this->record($news, $actor, $action, $news->rejection_reason);
    }
}