<?php

namespace App\Http\Middleware;

use App\Services\Access\AccessService;
use App\Support\AdminRoutePermission;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPermission
{
    public function __construct(
        private readonly AccessService $access
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user, 401, 'Silakan masuk terlebih dahulu.');

        abort_unless(
            $user->is_active,
            403,
            'Akun Anda tidak aktif.'
        );

        $name = $request->route()?->getName();

        abort_unless(
            is_string($name) && $name !== '',
            403,
            'Route belum memiliki aturan akses.'
        );

        $permissions = AdminRoutePermission::required($name);

        /*
         * Route yang belum dikenal selalu ditolak.
         * Termasuk ketika dibuka oleh Super Admin.
         */
        abort_if(
            $permissions === null,
            403,
            'Route belum memiliki aturan permission.'
        );

        foreach ($permissions as $permission) {
            abort_unless(
                $this->access->allows($user, $permission),
                403,
                'Anda tidak memiliki izin untuk tindakan ini.'
            );
        }

        return $next($request);
    }
}
