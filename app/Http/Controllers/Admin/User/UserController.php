<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Models\User;
use App\Services\Access\AccessService;
use App\Support\ManagedUsers;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private readonly AccessService $access
    ) {
    }

    private function actor(string $permission): User
    {
        $actor = request()->user();

        abort_unless(
            $actor
            && $actor->is_active
            && in_array(
                $this->access->role($actor),
                ['super-admin', 'admin'],
                true
            )
            && $this->access->allows($actor, $permission),
            403,
            'Anda tidak memiliki izin untuk tindakan ini.'
        );

        return $actor;
    }

    public function index(Request $request): View
    {
        $actor = $this->actor('users.view');

        $data = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'unit_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $search = trim($data['search'] ?? '');
        $unitId = (string) ($data['unit_id'] ?? '');
        $status = $data['status'] ?? '';

        $users = ManagedUsers::query($actor, 'users.view')
            ->with('unit')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'ilike', '%'.$search.'%')
                        ->orWhere('email', 'ilike', '%'.$search.'%')
                        ->orWhere('position', 'ilike', '%'.$search.'%')
                        ->orWhere('phone', 'ilike', '%'.$search.'%');
                });
            })
            ->when(
                $unitId !== '',
                fn ($query) => $query->where('unit_id', $unitId)
            )
            ->when(
                $status !== '',
                fn ($query) => $query->where(
                    'is_active',
                    $status === 'active'
                )
            )
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $units = ManagedUsers::units($actor, 'users.view')
            ->orderBy('name')->get();

        return view(
            'admin.users.index',
            compact('users', 'units', 'search', 'unitId', 'status')
        );
    }

    public function create(): View
    {
        $actor = $this->actor('users.create');

        $units = ManagedUsers::units($actor, 'users.create')
            ->orderBy('name')->get();

        return view('admin.users.create', compact('units'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            User::whereKey(2)->lockForUpdate()->firstOrFail();

            $actor = User::findOrFail($request->user()->id);

            abort_unless(
                $actor->is_active
                && in_array(
                    $this->access->role($actor),
                    ['super-admin', 'admin'],
                    true
                )
                && $this->access->allows($actor, 'users.create'),
                403
            );

            abort_unless(
                $this->access->allowsUnit(
                    $actor,
                    'users.create',
                    $data['unit_id'] ?? null
                ),
                403,
                'Unit pengguna berada di luar cakupan pembuatan akun.'
            );

            $levelId = DB::table('roles')
                ->where('slug', 'user')
                ->value('id');

            if ($levelId === null) {
                throw ValidationException::withMessages([
                    'name' => 'Level User belum tersedia.',
                ]);
            }

            $target = User::create($data);
            $now = now();

            DB::table('role_user')->insert([
                'user_id' => $target->id,
                'role_id' => $levelId,
                'data_scope' => 'own_unit',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('user_access_profiles')->insert([
                'user_id' => $target->id,
                'parent_user_id' => $actor->id,
                'data_scope' => 'own_unit',
                'is_enabled' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });

        return $this->afterSave(
            'Pengguna berhasil dibuat. Tetapkan role melalui Role & Akses.'
        );
    }

    public function edit(User $user): View
    {
        $actor = $this->actor('users.update');
        ManagedUsers::assertTarget($actor, $user, 'users.update');

        $user->load('unit');

        $units = ManagedUsers::units($actor, 'users.update')
            ->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'units'));
    }

    public function update(
        UpdateUserRequest $request,
        User $user
    ): RedirectResponse {
        $data = $request->validated();

        if (empty($data['password'])) {
            unset($data['password']);
        }

        DB::transaction(function () use ($request, $user, $data) {
            User::whereKey(2)->lockForUpdate()->firstOrFail();

            $actor = User::findOrFail($request->user()->id);
            $target = User::whereKey($user->id)
                ->lockForUpdate()->firstOrFail();

            ManagedUsers::assertTarget($actor, $target, 'users.update');

            if (
                (int) $target->id === 2
                && !($data['is_active'] ?? false)
            ) {
                throw ValidationException::withMessages([
                    'is_active' => 'Akun utama tidak boleh dinonaktifkan.',
                ]);
            }

            if (
                !$this->access->isSuperAdmin($actor)
                && (string) ($data['unit_id'] ?? '')
                    !== (string) ($target->unit_id ?? '')
            ) {
                throw ValidationException::withMessages([
                    'unit_id' =>
                        'Perubahan unit pengguna harus dilakukan Super Admin.',
                ]);
            }

            $target->update($data);
        });

        return $this->afterSave('Pengguna berhasil diperbarui.');
    }

    private function afterSave(string $message): RedirectResponse
    {
        $actor = request()->user();

        if ($this->access->allows($actor, 'users.view')) {
            return redirect()->route('admin.users.index')
                ->with('success', $message);
        }

        if ($this->access->allows($actor, 'access.view')) {
            return redirect()->route('admin.access.index')
                ->with('success', $message);
        }

        return back()->with('success', $message);
    }
}