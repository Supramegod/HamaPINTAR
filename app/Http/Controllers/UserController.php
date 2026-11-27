<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::query()
            ->search($request->string('q')->trim()->value())
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->input('role')))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'counts' => [
                'total' => User::count(),
                'admin' => User::where('role', UserRole::Admin)->count(),
                'inactive' => User::where('is_active', false)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        return view('users.form', ['user' => new User]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $user = User::create([
            ...$request->safe()->except('is_active'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return to_route('users.index')->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    public function edit(User $user): View
    {
        return view('users.form', ['user' => $user]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email']);

        if ($request->filled('password')) {
            $data['password'] = $request->validated('password');
        }

        // Peran & status akun sendiri tidak bisa diubah agar admin tidak mengunci dirinya.
        if (Gate::allows('modify-account-status', $user)) {
            $data['role'] = $request->validated('role');
            $data['is_active'] = $request->boolean('is_active');
        }

        $user->update($data);

        if (! $user->is_active) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }

        return to_route('users.index')->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        Gate::authorize('modify-account-status', $user);

        $user->delete();
        DB::table('sessions')->where('user_id', $user->id)->delete();

        return to_route('users.index')->with('success', "Pengguna {$user->name} telah dihapus.");
    }
}
