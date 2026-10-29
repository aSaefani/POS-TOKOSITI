<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\User\UserRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::query()->orderBy('name')->paginate(20),
            'roles' => Role::cases(),
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        User::create($request->safe()->except('password_confirmation') + [
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('users.index')->with('status', 'Pengguna berhasil ditambahkan.');
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        // Admin tidak boleh menonaktifkan atau menurunkan dirinya sendiri (kunci lockout).
        if ($user->is($request->user()) && (! $request->boolean('is_active', true) || $request->string('role')->value() !== Role::Admin->value)) {
            return back()->withErrors(['role' => 'Anda tidak dapat menurunkan peran atau menonaktifkan akun sendiri.']);
        }

        $data = $request->safe()->except('password_confirmation');

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');

        $user->update($data);

        return redirect()->route('users.index')->with('status', 'Data pengguna diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 422, 'Tidak dapat menghapus akun sendiri.');

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Pengguna dinonaktifkan.');
    }
}
