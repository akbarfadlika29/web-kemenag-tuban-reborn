<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Services\Access\AccessService;
use App\Support\ManagedUsers;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserManagementAccess
{
    public function __construct(
        private readonly AccessService $access
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->routeIs('admin.users.*')) {
            return $next($request);
        }

        $actor = $request->user();

        abort_unless(
            $actor
            && $actor->is_active
            && in_array(
                $this->access->role($actor),
                ['super-admin', 'admin'],
                true
            ),
            403,
            'Pengelolaan pengguna hanya untuk Admin dan Super Admin.'
        );

        if ($request->routeIs('admin.users.edit', 'admin.users.update')) {
            $target = $request->route('user');

            abort_unless($target instanceof User, 404);

            ManagedUsers::assertTarget($actor, $target, 'users.update');
        }

        return $next($request);
    }
}