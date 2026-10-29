@extends('layouts.app')

@section('title', 'Pengguna')
@section('heading', 'Manajemen Pengguna')
@section('subheading', 'Atur peran dan akses akun staf.')

@section('content')
<section class="grid gap-6 xl:grid-cols-3">
    <div class="card space-y-4 xl:col-span-1">
        <h2 class="font-bold">Tambah pengguna</h2>
        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="label" for="name">Nama</label>
                <input id="name" name="name" value="{{ old('name') }}" required maxlength="100" class="input">
            </div>
            <div>
                <label class="label" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required maxlength="191" class="input">
                @error('email')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="role">Peran</label>
                <select id="role" name="role" class="input">
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label" for="password">Kata sandi (min. 10, huruf & angka)</label>
                <input id="password" name="password" type="password" required autocomplete="new-password" class="input">
                @error('password')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="password_confirmation">Ulangi kata sandi</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="input">
            </div>
            <button class="btn-primary w-full">Simpan pengguna</button>
        </form>
    </div>

    <div class="card overflow-hidden p-0 xl:col-span-2">
        <div class="table-glass overflow-x-auto">
            <table class="w-full min-w-[34rem]">
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Peran</th>
                        <th scope="col">Status</th>
                        <th scope="col">Login terakhir</th>
                        <th scope="col"><span class="sr-only">Aksi</span></th>
                    </tr>
                </thead>
                <tbody x-data="{ editingUserId: null }">
                    @foreach ($users as $user)
                        <tr>
                            <td>
                                <p class="font-semibold text-ink-900">{{ $user->name }}</p>
                                <p class="text-xs text-ink-500">{{ $user->email }}</p>
                            </td>
                            <td>{{ $user->role->label() }}</td>
                            <td>
                                <x-badge :tone="$user->is_active ? 'success' : 'neutral'">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</x-badge>
                            </td>
                            <td class="whitespace-nowrap text-xs">{{ $user->last_login_at?->diffForHumans() ?? 'Belum pernah' }}</td>
                            <td class="text-right">
                                <button type="button" @click="editingUserId = editingUserId === {{ $user->id }} ? null : {{ $user->id }}" class="font-semibold text-brand-700 hover:underline">Ubah</button>
                            </td>
                        </tr>
                        <tr x-show="editingUserId === {{ $user->id }}" x-cloak x-transition>
                            <td colspan="5" class="bg-white/40 p-4">
                                <form method="POST" action="{{ route('users.update', $user) }}" class="grid gap-3 sm:grid-cols-5 sm:items-end">
                                    @csrf @method('PUT')
                                    <input name="name" value="{{ $user->name }}" required class="input" aria-label="Nama">
                                    <input name="email" type="email" value="{{ $user->email }}" required class="input" aria-label="Email">
                                    <select name="role" class="input" aria-label="Peran">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->value }}" @selected($user->role === $role)>{{ $role->label() }}</option>
                                        @endforeach
                                    </select>
                                    <input name="password" type="password" placeholder="Kata sandi baru (opsional)" autocomplete="new-password" class="input" aria-label="Kata sandi baru">
                                    <label class="flex items-center gap-2 text-sm">
                                        <input type="checkbox" name="is_active" value="1" @checked($user->is_active) class="rounded border-white text-brand-600">
                                        Aktif
                                    </label>
                                    <div class="sm:col-span-5 flex gap-2">
                                        <button class="btn-primary px-4 py-2">Simpan</button>
                                    </div>
                                </form>
                                @if (! $user->is(auth()->user()))
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="mt-2" x-data
                                          x-on:submit="if (!window.confirm('Nonaktifkan pengguna ini?')) $event.preventDefault()">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-rose-600 hover:underline">Nonaktifkan akun</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t border-white/60 px-4 py-3">{{ $users->links() }}</div>
    </div>
</section>
@endsection
