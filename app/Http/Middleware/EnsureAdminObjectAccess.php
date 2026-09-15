<?php

namespace App\Http\Middleware;

use App\Services\Access\AccessService;
use App\Support\AdminRoutePermission;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminObjectAccess
{
    private const CONTENT = [
        'news' => 'status',
        'pages' => 'status',
        'announcements' => 'status',
        'agendas' => 'status',
        'galleries' => 'status',
        'ppid-informations' => 'status',
        'services' => 'status',
        'regulations' => 'publication_status',
    ];

    public function __construct(
        private readonly AccessService $access
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user && $user->is_active, 403);

        $name = (string) $request->route()?->getName();
        $parts = explode('.', $name);
        $module = $parts[1] ?? '';
        $action = end($parts);
        $permissions = AdminRoutePermission::required($name);

        abort_if($permissions === null, 403);

        $permission = $permissions[0] ?? null;
        $contentField = self::CONTENT[$module] ?? null;

        /*
         * Chỉ kiểm tra model thuộc unit; các danh mục chung
         * vẫn tuân theo permission route.
         */
        $record = null;

        foreach ($request->route()->parameters() as $parameter) {
            if (!$parameter instanceof Model) {
                continue;
            }

            if (array_key_exists('unit_id', $parameter->getAttributes())) {
                if ($permission) {
                    abort_unless(
                        $this->access->allowsUnit(
                            $user,
                            $permission,
                            $parameter->getAttribute('unit_id')
                        ),
                        403,
                        'Anda tidak memiliki akses ke unit data ini.'
                    );
                }

                /*
                 * Model konten utama harus memiliki kolom status
                 * yang sesuai, bukan sekadar relasi media.
                 */
                if (
                    $contentField
                    && array_key_exists(
                        $contentField,
                        $parameter->getAttributes()
                    )
                ) {
                    $record = $parameter;
                }
            }
        }

        $mutating = !$request->isMethodSafe();

        if (
            $mutating
            && $permission
            && ($contentField || $module === 'media')
        ) {
            $scope = $this->access->scope($user, $permission);

            if ($scope === 'own_unit') {
                abort_unless(
                    $user->unit_id !== null,
                    403,
                    'Akun belum memiliki unit kerja.'
                );

                $submittedUnit = $request->input('unit_id');

                if (
                    $submittedUnit !== null
                    && $submittedUnit !== ''
                    && (
                        !is_scalar($submittedUnit)
                        || (string) $submittedUnit !== (string) $user->unit_id
                    )
                ) {
                    abort(403, 'Anda tidak boleh menggunakan unit lain.');
                }

                $request->merge(['unit_id' => $user->unit_id]);
            }
        }

        if ($contentField && $mutating) {
            $oldStatus = $record?->getAttribute($contentField);
            $newStatus = $request->input($contentField);

            if ($newStatus === 'published') {
                abort_unless(
                    $this->access->allowsUnit(
                        $user,
                        $module.'.publish',
                        $request->input('unit_id', $record?->unit_id)
                    ),
                    403,
                    'Anda tidak memiliki izin menerbitkan konten ini.'
                );
            }

            /*
             * Mengedit konten terbit juga mengubah informasi publik.
             * Tanpa izin publish, perubahan harus melalui revisi.
             */
            if ($oldStatus === 'published') {
                if (
                    $action === 'destroy'
                    || (
                        $newStatus !== null
                        && $newStatus !== 'published'
                    )
                ) {
                    abort_unless(
                        $this->access->allowsUnit(
                            $user,
                            $module.'.unpublish',
                            $record->unit_id
                        ),
                        403,
                        'Anda tidak memiliki izin menarik publikasi.'
                    );
                } else {
                    abort_unless(
                        $this->access->allowsUnit(
                            $user,
                            $module.'.publish',
                            $record->unit_id
                        ),
                        403,
                        'Perubahan konten terbit memerlukan izin publikasi.'
                    );
                }
            }
        }

        return $next($request);
    }
}
