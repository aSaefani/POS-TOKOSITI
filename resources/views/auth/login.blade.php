@extends('layouts.base')

@section('title', 'Masuk')

@section('body')
<main class="grid min-h-dvh place-items-center p-4">
    <div class="glass-strong animate-fade-up w-full max-w-md rounded-3xl p-8">
        <div class="mb-8 text-center">
            <img src="{{ asset('images/toko-siti-mark.svg') }}" alt="Logo Toko Siti" class="mx-auto size-10 rounded-xl object-contain">
            <h1 class="mt-4 text-2xl font-extrabold tracking-tight">TOKO SITI</h1>
            <p class="text-sm text-ink-500">Masuk untuk melanjutkan</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-5" x-data="{ show: false, loading: false }" @submit="loading = true">
            @csrf

            <div>
                <label for="email" class="label">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       autocomplete="username" maxlength="191" class="input" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
            </div>

            <div>
                <label for="password" class="label">Kata sandi</label>
                <div class="relative">
                    <input id="password" name="password" :type="show ? 'text' : 'password'" required
                           autocomplete="current-password" maxlength="200" class="input pr-16">
                    <button type="button" @click="show = !show"
                            class="absolute inset-y-0 right-2 my-1 rounded-lg px-2.5 text-xs font-semibold text-ink-500 transition hover:bg-white/70 hover:text-ink-900">
                        <span x-text="show ? 'Sembunyi' : 'Lihat'"></span>
                    </button>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm text-ink-700">
                <input type="checkbox" name="remember" value="1" class="rounded border-white text-brand-600 focus:ring-brand-500">
                Ingat saya di perangkat ini
            </label>

            @if ($errors->any())
                <p role="alert" class="animate-fade-up rounded-xl bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">
                    {{ $errors->first() }}
                </p>
            @endif

            <button type="submit" class="btn-primary w-full" :disabled="loading">
                <span x-show="!loading">Masuk</span>
                <span x-show="loading" x-cloak class="flex items-center gap-2">
                    <span class="size-4 animate-spin rounded-full border-2 border-white/40 border-t-white"></span> Memproses…
                </span>
            </button>
        </form>
    </div>
</main>
@endsection
