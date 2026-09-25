<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Access\AccessService;
use App\Support\PermissionCatalog;
use App\Support\PermissionPolicy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditPpidAccess extends Command
{
    protected $signature = 'ppid:access-audit';

    protected $description = 'Audit konsistensi RBAC, delegation, role ceiling, dan tenant/unit scope PPID';

    public function handle(AccessService $access): int
    {
        $issues = [];

        $this->auditPermissionCatalog($issues);
        $this->auditSystemRoles($access, $issues);
        $this->auditAccessRoles($access, $issues);
        $this->auditAssignments($access, $issues);

        if ($issues === []) {
            $this->info('RBAC AUDIT: OK');
            $this->line('Role, permission, delegation, dan tenant scope konsisten.');

            return self::SUCCESS;
        }

        $this->error('RBAC AUDIT: GAGAL ('.count($issues).' masalah)');

        foreach ($issues as $index => $issue) {
            $this->line(($index + 1).'. '.$issue);
        }

        return self::FAILURE;
    }


    private function auditPermissionCatalog(array &$issues): void
    {
        $catalog = collect(PermissionCatalog::permissions())
            ->pluck('slug')
            ->values();
        $database = DB::table('permissions')->pluck('slug')->values();

        foreach ($catalog->diff($database) as $slug) {
            $issues[] = "Permission katalog {$slug} belum tersinkron ke database.";
        }

        foreach ($database->diff($catalog) as $slug) {
            $issues[] = "Permission database {$slug} tidak lagi ada di katalog aktif.";
        }
    }

    private function auditSystemRoles(AccessService $access, array &$issues): void
    {
        $users = User::query()->get(['id', 'name', 'unit_id', 'is_active']);

        foreach ($users as $user) {
            $roles = DB::table('role_user')
                ->join('roles', 'roles.id', '=', 'role_user.role_id')
                ->where('role_user.user_id', $user->id)
                ->pluck('roles.slug')
                ->unique()
                ->values();

            if ($roles->count() !== 1) {
                $issues[] = "User #{$user->id} {$user->name}: system role harus tepat satu.";
                continue;
            }

            $role = (string) $roles->first();

            if (!in_array($role, ['super-admin', 'admin', 'user'], true)) {
                $issues[] = "User #{$user->id}: system role {$role} tidak dikenali.";
                continue;
            }

            if (!$user->is_active || $role === 'super-admin') {
                continue;
            }

            $profile = DB::table('user_access_profiles')
                ->where('user_id', $user->id)
                ->first(['parent_user_id', 'is_enabled']);

            if (!$profile) {
                $issues[] = "User #{$user->id}: profil delegasi tidak tersedia.";
                continue;
            }

            if (!$profile->is_enabled) {
                continue;
            }

            if (!$profile->parent_user_id) {
                $issues[] = "User #{$user->id}: parent pemberi akses belum ditetapkan.";
                continue;
            }

            $parent = User::find($profile->parent_user_id);

            if (!$parent || !$parent->is_active) {
                $issues[] = "User #{$user->id}: parent tidak tersedia atau tidak aktif.";
                continue;
            }

            $parentRole = $access->role($parent);

            if ($role === 'admin' && $parentRole !== 'super-admin') {
                $issues[] = "Admin #{$user->id}: parent wajib Super Admin.";
            }

            if (
                $role === 'user'
                && !in_array($parentRole, ['admin', 'super-admin'], true)
            ) {
                $issues[] = "User #{$user->id}: parent wajib Admin atau Super Admin.";
            }

            if (
                !DB::table('access_role_user')
                    ->where('user_id', $user->id)
                    ->exists()
            ) {
                $issues[] = "User #{$user->id}: access role belum ditetapkan.";
            }
        }
    }

    private function auditAccessRoles(AccessService $access, array &$issues): void
    {
        $roles = DB::table('access_roles')->get();

        foreach ($roles as $role) {
            $owner = User::find($role->owner_user_id);

            if (
                !$owner
                || !$owner->is_active
                || !in_array($access->role($owner), ['super-admin', 'admin'], true)
            ) {
                $issues[] = "Access role #{$role->id} {$role->name}: owner bukan manager aktif.";
                continue;
            }

            $grants = DB::table('access_role_permissions as arp')
                ->join('permissions as p', 'p.id', '=', 'arp.permission_id')
                ->where('arp.access_role_id', $role->id)
                ->get([
                    'p.slug',
                    'arp.data_scope',
                    'arp.can_delegate',
                ]);

            foreach ($grants as $grant) {
                if (!PermissionCatalog::has($grant->slug)) {
                    $issues[] = "Access role #{$role->id}: permission {$grant->slug} tidak ada di katalog.";
                    continue;
                }

                if (!PermissionPolicy::scopeAllowed($grant->slug, $grant->data_scope)) {
                    $issues[] = "Access role #{$role->id}: {$grant->slug} memakai scope {$grant->data_scope} yang tidak valid.";
                    continue;
                }

                $ownerLimit = $access->scope($owner, $grant->slug, true);

                if (
                    $ownerLimit === null
                    || (
                        $grant->data_scope === 'all_units'
                        && $ownerLimit !== 'all_units'
                    )
                ) {
                    $issues[] = "Access role #{$role->id}: {$grant->slug} melampaui delegation ceiling owner.";
                }

                if ($grant->can_delegate && $access->role($owner) !== 'super-admin') {
                    if ($ownerLimit === null) {
                        $issues[] = "Access role #{$role->id}: {$grant->slug} ditandai delegable tanpa delegation authority.";
                    }
                }
            }
        }
    }

    private function auditAssignments(AccessService $access, array &$issues): void
    {
        $assignments = DB::table('access_role_user as aru')
            ->join('access_roles as ar', 'ar.id', '=', 'aru.access_role_id')
            ->select(
                'aru.user_id',
                'aru.access_role_id',
                'ar.owner_user_id',
                'ar.is_active'
            )
            ->get();

        foreach ($assignments as $assignment) {
            $user = User::find($assignment->user_id);

            if (!$user) {
                $issues[] = "Assignment access role #{$assignment->access_role_id}: user tidak ditemukan.";
                continue;
            }

            if (!$assignment->is_active) {
                continue;
            }

            $systemRole = $access->role($user);
            $profile = DB::table('user_access_profiles')
                ->where('user_id', $user->id)
                ->first(['parent_user_id', 'is_enabled']);

            if (!$profile || !$profile->is_enabled || !$profile->parent_user_id) {
                continue;
            }

            $parent = User::find($profile->parent_user_id);
            $owner = User::find($assignment->owner_user_id);

            if (!$parent || !$owner) {
                $issues[] = "User #{$user->id}: parent atau owner access role tidak ditemukan.";
                continue;
            }

            if (
                (int) $owner->id !== (int) $parent->id
                && !$access->isSuperAdmin($owner)
            ) {
                $issues[] = "User #{$user->id}: access role bukan milik parent langsung/Super Admin.";
            }

            $grants = DB::table('access_role_permissions as arp')
                ->join('permissions as p', 'p.id', '=', 'arp.permission_id')
                ->where('arp.access_role_id', $assignment->access_role_id)
                ->get(['p.slug', 'arp.data_scope']);

            foreach ($grants as $grant) {
                if (!PermissionPolicy::systemRoleAllows($systemRole, $grant->slug)) {
                    $issues[] = "User #{$user->id}: {$grant->slug} melampaui ceiling system role {$systemRole}.";
                    continue;
                }

                if (!PermissionPolicy::scopeAllowed($grant->slug, $grant->data_scope)) {
                    $issues[] = "User #{$user->id}: {$grant->slug} memiliki scope role yang tidak valid.";
                    continue;
                }

                $parentScope = $access->scope($parent, $grant->slug, true);

                if ($parentScope === null) {
                    $issues[] = "User #{$user->id}: parent tidak lagi dapat mendelegasikan {$grant->slug}.";
                    continue;
                }

                if (
                    $parentScope === 'own_unit'
                    && (
                        $grant->data_scope !== 'own_unit'
                        || $parent->unit_id === null
                        || $user->unit_id === null
                        || (string) $parent->unit_id !== (string) $user->unit_id
                    )
                ) {
                    $issues[] = "User #{$user->id}: {$grant->slug} melampaui own_unit parent.";
                }
            }
        }
    }
}
