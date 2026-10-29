@extends('layouts.base')

@section('body')
@php
    $user = auth()->user();

    $nav = [
        ['route' => 'dashboard', 'label' => 'Dashboard', 'permission' => 'dashboard.view', 'icon' => 'M3 13h8V3H3v10Zm0 8h8v-6H3v6Zm10 0h8V11h-8v10Zm0-18v6h8V3h-8Z'],
        ['route' => 'pos.index', 'label' => 'Kasir (POS)', 'permission' => 'pos.use', 'icon' => 'M3 6h18v4H3V6Zm2 6h14v8H5v-8Zm3 2v4h2v-4H8Zm4 0v4h2v-4h-2Z'],
        ['route' => 'transactions.index', 'label' => 'Transaksi', 'permission' => 'transactions.view_own', 'icon' => 'M6 2h9l5 5v15H6V2Zm8 1.5V8h4.5L14 3.5ZM8 12h8v2H8v-2Zm0 4h8v2H8v-2Z'],
        ['route' => 'products.index', 'label' => 'Produk', 'permission' => 'products.manage', 'icon' => 'M12 2 3 7v10l9 5 9-5V7l-9-5Zm0 2.3L18.5 8 12 11.7 5.5 8 12 4.3Z'],
        ['route' => 'users.index', 'label' => 'Pengguna', 'permission' => 'users.manage', 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0H5Z'],
    ];
@endphp

<div class="flex min-h-dvh" x-data="{ open: false }">
    {{-- Sidebar: desktop tetap, mobile sebagai drawer --}}
    <aside class="glass-strong fixed inset-y-3 left-3 z-40 flex w-64 flex-col rounded-3xl p-4 transition-transform duration-300 ease-glass lg:translate-x-0"
           :class="open ? 'translate-x-0' : '-translate-x-[110%] lg:translate-x-0'">
        <a href="{{ route($user->role->homeRoute()) }}" class="flex items-center gap-3 px-2 py-3">
            <img src="{{ asset('images/toko-siti-mark.svg') }}" alt="Logo Toko Siti" class="size-10 rounded-2xl object-contain">
            <span>
                <span class="block text-base font-extrabold leading-tight">TOKO SITI</span>
                <span class="block text-xs font-medium text-ink-500">Point of Sale</span>
            </span>
        </a>

        <nav class="mt-6 flex flex-1 flex-col gap-1 thin-scroll" aria-label="Menu utama">
            @foreach ($nav as $item)
                @continue(! $user->hasPermission($item['permission']))
                @php $active = request()->routeIs($item['route']) || request()->routeIs(str_replace('.index', '.*', $item['route'])); @endphp
                <a href="{{ route($item['route']) }}"
                   @class([
                       'group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold transition duration-200 ease-glass',
                       'bg-white text-brand-700 shadow-md' => $active,
                       'text-ink-700 hover:translate-x-1 hover:bg-white/60' => ! $active,
                   ])
                   @if($active) aria-current="page" @endif>
                    <svg class="size-5 shrink-0 fill-current transition group-hover:scale-110" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $item['icon'] }}"/></svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="mt-4 space-y-3 border-t border-white/60 pt-4">
            <div class="flex items-center gap-3 rounded-xl px-2 py-2">
                <span class="grid size-9 place-items-center rounded-full bg-brand-100 text-sm font-bold text-brand-700">
                    {{ mb_substr($user->name, 0, 1) }}
                </span>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold">{{ $user->name }}</p>
                    <p class="text-xs text-ink-500">{{ $user->role->label() }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-ghost w-full justify-start text-rose-600">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div x-show="open" x-cloak x-transition.opacity @click="open = false"
         class="fixed inset-0 z-30 bg-slate-900/30 backdrop-blur-sm lg:hidden"></div>

    <main class="min-w-0 flex-1 p-4 lg:ml-72 lg:p-8">
        <header class="mb-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <button type="button" class="btn-ghost lg:hidden" @click="open = true" aria-label="Buka menu">
                    <svg class="size-5 fill-current" viewBox="0 0 24 24"><path d="M3 6h18v2H3V6Zm0 5h18v2H3v-2Zm0 5h18v2H3v-2Z"/></svg>
                </button>
                <div class="animate-fade-up">
                    <h1 class="text-2xl font-extrabold tracking-tight">@yield('heading')</h1>
                    @hasSection('subheading')
                        <p class="text-sm text-ink-500">@yield('subheading')</p>
                    @endif
                </div>
            </div>
            @yield('actions')
        </header>

        <div>
            @yield('content')
        </div>
    </main>
</div>
@endsection
