<?php

namespace App\Http\Controllers\Admin\Access;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Access\AccessService;
use App\Support\PermissionCatalog;
use App\Support\PermissionPolicy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserAccessController extends Controller
{
    public function __construct(
        private readonly AccessService $access
    ) {
    }

    private function assertManager(User $actor): void
    {
        abort_unless(
            in_array($this->access->role($actor), ['super-admin', 'admin'], true),
            403,
            'Hanya Admin dan Super Admin yang dapat mengelola akses.'
        );
    }

    private function assertTarget(User $actor, User $target): void
    {
        $this->assertManager($actor);

        abort_if(
            (int) $target->id === 2
            || (int) $target->id === (int) $actor->id,
            403,
            'Akses akun utama atau akun sendiri tidak dapat diubah di sini.'
        );

        if ($this->access->isSuperAdmin($actor)) {
            abort_if(
                $this->access->role($target) === 'super-admin',
                403,
                'Role Super Admin tidak dikelola melalui halaman ini.'
            );

            return;
        }

        abort_unless(
            $this->access->role($target) === 'user',
            403,
            'Admin hanya dapat mengelola role User.'
        );

        abort_unless(
            DB::table('user_access_profiles')
                ->where('user_id', $target->id)
                ->where('parent_user_id', $actor->id)
                ->exists(),
            403,
            'Pengguna ini bukan bawahan Anda.'
        );

        abort_unless(
            $this->access->allowsUnit($actor, 'access.assign', $target->unit_id),
            403,
            'Pengguna berada di luar cakupan pengelolaan akses Anda.'
        );
    }

    public function index(Request $request)
    {
        $actor = $request->user();
        $this->assertManager($actor);

        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
        ]);

        $search = trim($data['search'] ?? '');
        $query = User::query()->with('unit')->orderBy('name');

        if (!$this->access->isSuperAdmin($actor)) {
            $query->whereIn(
                'id',
                DB::table('user_access_profiles')
                    ->select('user_id')
                    ->where('parent_user_id', $actor->id)
            )->whereIn(
                'id',
                DB::table('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->select('role_user.user_id')
                    ->where('roles.slug', 'user')
            );

            if ($this->access->scope($actor, 'access.view') === 'own_unit') {
                $query->where('unit_id', $actor->unit_id);
            }
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'ilike', '%'.$search.'%')
                    ->orWhere('email', 'ilike', '%'.$search.'%');
            });
        }

        return view('admin.access.index', [
            'users' => $query->paginate(20)->withQueryString(),
            'search' => $search,
            'accessService' => $this->access,
        ]);
    }

    public function edit(Request $request, User $user)
    {
        $actor = $request->user();
        $this->assertTarget($actor, $user);
        $isSuper = $this->access->isSuperAdmin($actor);

        $parents = $isSuper
            ? User::where('is_active', true)
                ->where('id', '!=', $user->id)
                ->whereIn('id', DB::table('role_user')
                    ->join('roles', 'roles.id', '=', 'role_user.role_id')
                    ->select('role_user.user_id')
                    ->whereIn('roles.slug', ['super-admin', 'admin']))
                ->orderBy('name')->get()
            : collect([$actor]);

        $roles = DB::table('access_roles as r')
            ->join('users as u', 'u.id', '=', 'r.owner_user_id')
            ->where('r.is_active', true)
            ->select('r.*', 'u.name as owner_name')
            ->orderBy('r.name');

        if (!$isSuper) {
            $roles->where(function ($query) use ($actor) {
                $query->where('r.owner_user_id', $actor->id)
                    ->orWhereIn('r.owner_user_id', DB::table('role_user')
                        ->join('roles', 'roles.id', '=', 'role_user.role_id')
                        ->select('role_user.user_id')
                        ->where('roles.slug', 'super-admin'));
            });
        }

        return view('admin.access.edit', [
            'target' => $user->load('unit'),
            'profile' => DB::table('user_access_profiles')->where('user_id', $user->id)->first(),
            'currentRole' => $this->access->role($user) ?? 'user',
            'assignedRoleId' => DB::table('access_role_user')->where('user_id', $user->id)->value('access_role_id'),
            'accessRoles' => $roles->get(),
            'parents' => $parents,
            'isSuper' => $isSuper,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role' => ['required', 'in:admin,user'],
            'parent_user_id' => ['required', 'integer', 'exists:users,id'],
            'access_role_id' => ['required', 'integer', 'exists:access_roles,id'],
            'is_enabled' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $user, $data) {
            User::whereKey(2)->lockForUpdate()->firstOrFail();

            $actor = User::findOrFail($request->user()->id);
            $target = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            $this->assertTarget($actor, $target);
            abort_unless($this->access->allows($actor, 'access.assign'), 403);

            $isSuper = $this->access->isSuperAdmin($actor);

            if (!$isSuper) {
                abort_unless(
                    $data['role'] === 'user'
                    && (int) $data['parent_user_id'] === (int) $actor->id,
                    403,
                    'Admin hanya boleh mengatur User bawahannya.'
                );
            }

            $parent = User::findOrFail($data['parent_user_id']);
            $parentLevel = $this->access->role($parent);

            $this->requireValid(
                $parent->is_active
                && (int) $parent->id !== (int) $target->id
                && (
                    ($data['role'] === 'admin' && $parentLevel === 'super-admin')
                    || ($data['role'] === 'user'
                        && in_array($parentLevel, ['super-admin', 'admin'], true))
                ),
                'parent_user_id',
                'Pemberi akses tidak sesuai hierarki akun.'
            );

            if (
                $data['role'] === 'user'
                && $this->access->role($target) === 'admin'
            ) {
                $this->requireValid(
                    !DB::table('user_access_profiles')->where('parent_user_id', $target->id)->exists()
                    && !DB::table('access_roles')->where('owner_user_id', $target->id)->exists(),
                    'role',
                    'Pindahkan bawahan dan role milik Admin sebelum menurunkan tingkat akunnya.'
                );
            }

            $role = DB::table('access_roles')
                ->where('id', $data['access_role_id'])->lockForUpdate()->first();

            $this->requireValid(
                $role && $role->is_active,
                'access_role_id',
                'Role akses tidak aktif.'
            );

            $owner = User::find($role->owner_user_id);

            $this->requireValid(
                $owner && $owner->is_active
                && (
                    (int) $owner->id === (int) $parent->id
                    || $this->access->isSuperAdmin($owner)
                ),
                'access_role_id',
                'Role harus milik pemberi akses atau Super Admin.'
            );

            $grants = DB::table('access_role_permissions')
                ->join('permissions', 'permissions.id', '=', 'access_role_permissions.permission_id')
                ->where('access_role_id', $role->id)
                ->get(['permissions.slug', 'access_role_permissions.data_scope']);

            foreach ($grants as $grant) {
                $this->requireValid(
                    PermissionPolicy::systemRoleAllows($data['role'], $grant->slug),
                    'access_role_id',
                    'Role akses memuat permission yang tidak boleh dimiliki system role '.$data['role'].'.'
                );

                $this->requireValid(
                    PermissionPolicy::scopeAllowed($grant->slug, $grant->data_scope),
                    'access_role_id',
                    'Role akses memuat scope yang tidak sesuai jenis resource.'
                );

                $this->assertScope(
                    $this->access->scope($parent, $grant->slug, true),
                    $parent,
                    $target,
                    $grant->data_scope
                );

                $this->requireValid(
                    $grant->data_scope !== 'own_unit' || $target->unit_id !== null,
                    'access_role_id',
                    'Role memiliki izin unit sendiri. Tetapkan unit pengguna terlebih dahulu.'
                );
            }

            $levelId = DB::table('roles')->where('slug', $data['role'])->value('id');
            $this->requireValid($levelId !== null, 'role', 'Tingkat akun belum tersedia.');

            DB::table('role_user')->where('user_id', $target->id)->delete();
            DB::table('role_user')->insert([
                'user_id' => $target->id,
                'role_id' => $levelId,
                'data_scope' => 'own_unit',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('user_access_profiles')->upsert([[
                'user_id' => $target->id,
                'parent_user_id' => $parent->id,
                'data_scope' => 'own_unit',
                'is_enabled' => $request->boolean('is_enabled'),
                'created_at' => now(),
                'updated_at' => now(),
            ]], ['user_id'], ['parent_user_id', 'is_enabled', 'updated_at']);

            DB::table('access_role_user')->upsert([[
                'user_id' => $target->id,
                'access_role_id' => $role->id,
                'assigned_by' => $actor->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]], ['user_id'], ['access_role_id', 'assigned_by', 'updated_at']);
        });

        return back()->with('success', 'Role akses pengguna berhasil disimpan.');
    }

    private function assertScope(
        ?string $scope,
        User $parent,
        User $target,
        string $requested
    ): void {
        $valid = $scope === 'all_units'
            || (
                $scope === 'own_unit'
                && $requested === 'own_unit'
                && $parent->unit_id !== null
                && $target->unit_id !== null
                && (string) $parent->unit_id === (string) $target->unit_id
            );

        $this->requireValid(
            $valid,
            'granted',
            'Izin atau cakupan yang diminta melebihi kewenangan pemberi akses.'
        );
    }

    private function requireValid(bool $condition, string $field, string $message): void
    {
        if (!$condition) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }
}
