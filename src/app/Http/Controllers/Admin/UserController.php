<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\ActivityRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('activo', $request->boolean('inactivos') ? false : true)->orderBy('name');

        if ($request->filled('buscar')) {
            $search = $request->input('buscar');
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = in_array((string) $request->input('per_page'), ['15', '30', '50'], true)
            ? (int) $request->input('per_page')
            : 15;
        $users = $query->paginate($perPage)->withQueryString();

        if ($request->expectsJson()) {
            return response()->json($users);
        }

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request)
    {
        return DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            ActivityRecorder::recordAfterCommit(
                $request->user(), 'user.created', 'User created', "User {$user->name} was created.", $user
            );

            return redirect()->route('admin.users.index')
                ->with('success', 'Usuario creado correctamente.');
        });
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        } else {
            unset($data['password']);
        }

        return DB::transaction(function () use ($data, $request, $user) {
            $this->lockAdminMutations();
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($user->role !== $data['role']) {
                abort_if($request->user()->is($user), 403, 'No puedes cambiar tu propio rol.');
                if ($user->activo && $user->isAdmin() && $data['role'] !== User::ROLE_ADMIN) {
                    $this->ensureAnotherActiveAdmin();
                }
            }
            $user->update($data);

            ActivityRecorder::recordAfterCommit(
                $request->user(), 'user.updated', 'User updated', "User {$user->name} was updated.", $user
            );

            return redirect()->route('admin.users.index')
                ->with('success', 'Usuario actualizado correctamente.');
        });
    }

    public function deactivate(Request $request, User $user)
    {
        return $this->setActive($request, $user, false);
    }

    public function activate(Request $request, User $user)
    {
        return $this->setActive($request, $user, true);
    }

    private function setActive(Request $request, User $user, bool $active)
    {
        return DB::transaction(function () use ($request, $user, $active) {
            $this->lockAdminMutations();
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_if($request->user()->is($user), 403, 'No puedes desactivar tu propia cuenta.');
            if (! $active && $user->activo && $user->isAdmin()) {
                $this->ensureAnotherActiveAdmin();
            }
            $user->update(['activo' => $active]);

            return redirect()->route('admin.users.index')->with('success', $active ? 'Usuario activado correctamente.' : 'Usuario desactivado correctamente.');
        });
    }

    private function lockAdminMutations(): void
    {
        User::where('role', User::ROLE_ADMIN)->orderBy('id')->lockForUpdate()->firstOrFail();
    }

    private function ensureAnotherActiveAdmin(): void
    {
        abort_if(User::where('role', User::ROLE_ADMIN)->where('activo', true)->count() < 2, 422, 'Debe permanecer al menos un administrador activo.');
    }
}
