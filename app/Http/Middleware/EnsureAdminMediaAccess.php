<?php

namespace App\Http\Middleware;

use App\Models\Media;
use App\Services\Access\AccessService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminMediaAccess
{
    public function __construct(
        private readonly AccessService $access
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user && $user->is_active, 403);

        $ids = [];

        /*
         * Periksa semua field media_id, termasuk field bersarang:
         * items.*.media_id, documents.*.media_id, settings.logo_media_id.
         */
        if (!$request->isMethodSafe()) {
            $this->collectIds($request->input(), $ids);
        }

        if ($request->routeIs('admin.regulations.media.preview')) {
            $parameter = $request->route('media');

            $this->addId(
                $parameter instanceof Media ? $parameter->id : $parameter,
                'media',
                $ids
            );
        }

        $ids = array_values(array_unique($ids));

        if ($ids) {
            /*
             * Query Eloquent mengecualikan media yang sudah soft-delete.
             */
            $media = Media::query()->whereIn('id', $ids)->get();

            abort_unless(
                $media->count() === count($ids),
                403,
                'Salah satu media tidak tersedia atau tidak dapat digunakan.'
            );

            foreach ($media as $item) {
                abort_unless(
                    $this->access->allowsUnit(
                        $user,
                        'media.view',
                        $item->unit_id
                    ),
                    403,
                    'Anda tidak memiliki akses untuk menggunakan media tersebut.'
                );
            }
        }

        /*
         * Endpoint upload memakai permission media.upload,
         * meskipun berada di dalam modul regulasi.
         */
        if ($request->routeIs(
            'admin.media.store',
            'admin.media.picker.upload',
            'admin.regulations.media.upload'
        )) {
            $scope = $this->access->scope($user, 'media.upload');

            abort_unless($scope !== null, 403, 'Anda tidak boleh mengunggah media.');

            if ($scope === 'own_unit') {
                abort_unless($user->unit_id !== null, 403, 'Akun belum memiliki unit.');

                $submitted = $request->input('unit_id');

                if (
                    $submitted !== null
                    && $submitted !== ''
                    && (
                        !is_scalar($submitted)
                        || (string) $submitted !== (string) $user->unit_id
                    )
                ) {
                    abort(403, 'Unggahan hanya diizinkan untuk unit Anda.');
                }

                $request->merge(['unit_id' => $user->unit_id]);
            }
        }

        return $next($request);
    }

    private function collectIds(array $input, array &$ids, string $prefix = ''): void
    {
        foreach ($input as $key => $value) {
            $key = (string) $key;
            $path = $prefix === '' ? $key : $prefix.'.'.$key;

            if ($key === 'media_id' || str_ends_with($key, '_media_id')) {
                $this->addId($value, $path, $ids);
                continue;
            }

            if ($key === 'media_ids' || str_ends_with($key, '_media_ids')) {
                if ($value === null || $value === '') continue;

                if (!is_array($value)) {
                    throw ValidationException::withMessages([
                        $path => 'Daftar media harus berupa array.',
                    ]);
                }

                foreach ($value as $index => $id) {
                    $this->addId($id, $path.'.'.$index, $ids);
                }

                continue;
            }

            if (is_array($value)) {
                $this->collectIds($value, $ids, $path);
            }
        }
    }

    private function addId(mixed $value, string $field, array &$ids): void
    {
        if ($value === null || $value === '') return;

        if (
            (!is_string($value) && !is_int($value))
            || !preg_match('/^[1-9][0-9]*$/D', (string) $value)
        ) {
            throw ValidationException::withMessages([
                $field => 'ID media tidak valid.',
            ]);
        }

        $ids[] = (string) $value;
    }
}
