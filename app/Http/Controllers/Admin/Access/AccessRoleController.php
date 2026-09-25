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

class AccessRoleController extends Controller
{
    public function __construct(private readonly AccessService $access)
    {
    }

    private function manager(User $actor): void
    {
        abort_unless(
            $actor->is_active
            && in_array($this->access->role($actor), ['super-admin', 'admin'], true),
            403
        );
    }

    private function editable(User $actor, object $role): void
    {
        $this->manager($actor);

        abort_unless(
            $this->access->isSuperAdmin($actor)
            || (int) $role->owner_user_id === (int) $actor->id,
            403,
            'Role ini dikelola oleh pemberi akses lain.'
        );

        abort_if(
            DB::table('access_role_user')
                ->where('user_id', $actor->id)
                ->where('access_role_id', $role->id)
                ->exists(),
            403,
            'Anda tidak boleh mengubah role akses yang Anda gunakan sendiri.'
        );
    }

    public function index(Request $request)
    {
        $actor = $request->user();
        $this->manager($actor);

        $data = $request->validate([
            'edit' => ['nullable', 'integer', 'min:1'],
            'new' => ['nullable', 'boolean'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = DB::table('access_roles as r')
            ->join('users as u', 'u.id', '=', 'r.owner_user_id')
            ->select('r.*', 'u.name as owner_name')
            ->selectSub(
                DB::table('access_role_user')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('access_role_id', 'r.id'),
                'users_count'
            )
            ->orderBy('r.name');

        if (!$this->access->isSuperAdmin($actor)) {
            $query->where('r.owner_user_id', $actor->id);
        }

        $editing = null;
        $selected = collect();
        $showForm = !empty($data['new']) || !empty($data['edit']);

        if (!empty($data['edit'])) {
            abort_unless($this->access->allows($actor, 'roles.update'), 403);

            $editing = DB::table('access_roles')->where('id', $data['edit'])->first();
            abort_unless($editing, 404);
            $this->editable($actor, $editing);

            $selected = DB::table('access_role_permissions')
                ->where('access_role_id', $editing->id)
                ->get()->keyBy('permission_id');
        } elseif ($showForm) {
            abort_unless($this->access->allows($actor, 'roles.create'), 403);
        }

        return view('admin.access.roles', [
            'roles' => $query->paginate(15)->withQueryString(),
            'editing' => $editing,
            'selected' => $selected,
            'showForm' => $showForm,
            'permissions' => DB::table('permissions')
                ->orderBy('module')->orderBy('id')->get()->groupBy('module'),
            'modules' => PermissionCatalog::modules(),
            'accessService' => $this->access,
        ]);
    }

    public function store(Request $request)
    {
        return $this->save($request, null);
    }

    public function update(Request $request, int $accessRole)
    {
        return $this->save($request, $accessRole);
    }

    private function save(Request $request, ?int $id)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'scopes' => ['required', 'array', 'max:300'],
            'scopes.*' => ['required', 'in:none,own_unit,all_units'],
            'delegable' => ['sometimes', 'array', 'max:300'],
            'delegable.*' => ['required', 'integer', 'distinct'],
        ]);

        $savedId = DB::transaction(function () use ($request, $data, $id) {
            User::whereKey(2)->lockForUpdate()->firstOrFail();

            $actor = User::findOrFail($request->user()->id);
            $this->manager($actor);

            abort_unless(
                $this->access->allows($actor, $id ? 'roles.update' : 'roles.create'),
                403
            );

            $existing = $id
                ? DB::table('access_roles')->where('id', $id)->lockForUpdate()->first()
                : null;

            if ($id) {
                abort_unless($existing, 404);
                $this->editable($actor, $existing);
            }

            $owner = $existing
                ? User::findOrFail($existing->owner_user_id)
                : $actor;

            $this->valid(
                $owner->is_active
                && in_array($this->access->role($owner), ['super-admin', 'admin'], true),
                'name',
                'Pemilik role harus berupa Admin atau Super Admin aktif.'
            );

            $duplicates = DB::table('access_roles')
                ->where('owner_user_id', $owner->id)
                ->where('name', $data['name']);

            if ($id) $duplicates->where('id', '!=', $id);

            $this->valid(!$duplicates->exists(), 'name', 'Nama role sudah digunakan.');

            $catalog = DB::table('permissions')->get()->keyBy('id');
            $delegable = array_map('strval', $data['delegable'] ?? []);
            $rows = [];

            foreach ($data['scopes'] as $permissionId => $scope) {
                $permission = $catalog->get($permissionId);

                $this->valid(
                    $permission !== null,
                    'scopes',
                    'Permission tidak dikenal.'
                );

                if ($scope === 'none') {
                    continue;
                }

                $this->valid(
                    PermissionPolicy::scopeAllowed($permission->slug, $scope),
                    'scopes',
                    'Scope '.$permission->name.' tidak sesuai jenis resource.'
                );

                $limit = $this->access->scope($owner, $permission->slug, true);

                $this->valid(
                    $limit === 'all_units'
                    || ($limit === 'own_unit' && $scope === 'own_unit'),
                    'scopes',
                    'Cakupan '.$permission->name.' melebihi izin delegasi pemilik role.'
                );

                $rows[(string) $permissionId] = [
                    'permission_id' => $permissionId,
                    'data_scope' => $scope,
                    'can_delegate' => in_array((string) $permissionId, $delegable, true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach ($delegable as $permissionId) {
                $this->valid(
                    isset($rows[$permissionId]),
                    'delegable',
                    'Delegasi hanya boleh dipilih untuk permission yang diaktifkan.'
                );
            }

            $values = [
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $request->boolean('is_active'),
                'updated_at' => now(),
            ];

            if ($id) {
                DB::table('access_roles')->where('id', $id)->update($values);
            } else {
                $id = DB::table('access_roles')->insertGetId([
                    ...$values,
                    'owner_user_id' => $owner->id,
                    'created_at' => now(),
                ]);
            }

            DB::table('access_role_permissions')->where('access_role_id', $id)->delete();

            foreach ($rows as $row) {
                DB::table('access_role_permissions')->insert([
                    ...$row,
                    'access_role_id' => $id,
                ]);
            }

            return $id;
        });

        return redirect()->route('admin.access-roles.index', ['edit' => $savedId])
            ->with('success', 'Role akses berhasil disimpan.');
    }

    private function valid(bool $condition, string $field, string $message): void
    {
        if (!$condition) {
            throw ValidationException::withMessages([$field => $message]);
        }
    }
}
