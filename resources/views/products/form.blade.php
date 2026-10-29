@extends('layouts.app')

@php $isEdit = $product->exists; @endphp

@section('title', $isEdit ? 'Ubah Produk' : 'Produk Baru')
@section('heading', $isEdit ? 'Ubah Produk' : 'Produk Baru')
@section('subheading', $isEdit ? $product->name : 'Isi data produk dengan lengkap.')

@section('content')
<form method="POST" enctype="multipart/form-data"
      action="{{ $isEdit ? route('products.update', $product) : route('products.store') }}"
      class="card grid gap-5 sm:grid-cols-2 lg:max-w-4xl">
    @csrf
    @if ($isEdit) @method('PUT') @endif

    <div>
        <label class="label" for="name">Nama produk</label>
        <input id="name" name="name" value="{{ old('name', $product->name) }}" required maxlength="150" class="input">
        @error('name')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="category">Kategori</label>
        <input id="category" name="category" value="{{ old('category', $product->category) }}" required maxlength="80" class="input" list="category-list">
        <datalist id="category-list"><option value="Minuman"><option value="Snack"><option value="Sembako"></datalist>
        @error('category')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="sku">SKU</label>
        <input id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required maxlength="40" class="input font-mono">
        @error('sku')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="barcode">Barcode</label>
        <input id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}" maxlength="64" class="input font-mono" inputmode="numeric">
        @error('barcode')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="price">Harga jual (Rp)</label>
        <input id="price" name="price" type="number" min="0" value="{{ old('price', $product->price) }}" required class="input tabular-nums">
        @error('price')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="cost">Harga modal (Rp)</label>
        <input id="cost" name="cost" type="number" min="0" value="{{ old('cost', $product->cost) }}" class="input tabular-nums">
        @error('cost')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="stock">Stok</label>
        <input id="stock" name="stock" type="number" min="0" value="{{ old('stock', $product->stock) }}" required class="input tabular-nums">
        @error('stock')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="label" for="stock_alert_threshold">Batas peringatan stok</label>
        <input id="stock_alert_threshold" name="stock_alert_threshold" type="number" min="0"
               value="{{ old('stock_alert_threshold', $product->stock_alert_threshold ?? 5) }}" required class="input tabular-nums">
        @error('stock_alert_threshold')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label class="label" for="image">Foto (opsional, maks 2 MB)</label>
        @if ($product->image_url)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="mb-3 size-28 rounded-2xl bg-white/70 object-contain p-2">
        @endif
        <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="input file:mr-3 file:rounded-lg file:border-0 file:bg-brand-100 file:px-3 file:py-1.5 file:font-semibold file:text-brand-700">
        @error('image')<p class="mt-1 text-xs text-rose-700">{{ $message }}</p>@enderror
    </div>

    <label class="flex items-center gap-2 text-sm text-ink-700 sm:col-span-2">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="rounded border-white text-brand-600 focus:ring-brand-500">
        Tampilkan di kasir
    </label>

    <div class="flex gap-3 sm:col-span-2">
        <button class="btn-primary">Simpan</button>
        <a href="{{ route('products.index') }}" class="btn-ghost">Batal</a>
    </div>
</form>
@endsection
