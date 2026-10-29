<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Kata sandi default HANYA untuk pengembangan. Ganti sebelum production.
        $password = Hash::make('ChangeMe2026!');

        User::firstOrCreate(['email' => 'admin@tokositi.test'], [
            'name' => 'Siti Administrator', 'password' => $password, 'role' => Role::Admin, 'is_active' => true,
        ]);
        User::firstOrCreate(['email' => 'owner@tokositi.test'], [
            'name' => 'Owner Toko', 'password' => $password, 'role' => Role::Owner, 'is_active' => true,
        ]);
        User::firstOrCreate(['email' => 'kasir@tokositi.test'], [
            'name' => 'Kasir Pagi', 'password' => $password, 'role' => Role::Kasir, 'is_active' => true,
        ]);

        $products = [
            ['sku' => 'MIN-001', 'barcode' => '8990001000011', 'name' => 'Kopi Susu Botol 250ml', 'category' => 'Minuman', 'price' => 12000, 'cost' => 7500, 'stock' => 48, 'stock_alert_threshold' => 10, 'image_path' => 'catalog/MIN-001.png'],
            ['sku' => 'MIN-002', 'barcode' => '8990001000028', 'name' => 'Teh Melati Dingin', 'category' => 'Minuman', 'price' => 8000, 'cost' => 4200, 'stock' => 6, 'stock_alert_threshold' => 10, 'image_path' => 'catalog/MIN-002.png'],
            ['sku' => 'MIN-003', 'barcode' => '8990001000035', 'name' => 'Air Mineral 600ml', 'category' => 'Minuman', 'price' => 4000, 'cost' => 2400, 'stock' => 120, 'stock_alert_threshold' => 24, 'image_path' => 'catalog/MIN-003.png'],
            ['sku' => 'SNK-001', 'barcode' => '8990001000042', 'name' => 'Keripik Singkong Original', 'category' => 'Snack', 'price' => 10000, 'cost' => 6200, 'stock' => 3, 'stock_alert_threshold' => 8, 'image_path' => 'catalog/SNK-001.png'],
            ['sku' => 'SNK-002', 'barcode' => '8990001000059', 'name' => 'Biskuit Cokelat Kotak', 'category' => 'Snack', 'price' => 15500, 'cost' => 10100, 'stock' => 22, 'stock_alert_threshold' => 6, 'image_path' => 'catalog/SNK-002.png'],
            ['sku' => 'SEMBAKO-01', 'barcode' => '8990001000066', 'name' => 'Beras Premium 5kg', 'category' => 'Sembako', 'price' => 76000, 'cost' => 64000, 'stock' => 14, 'stock_alert_threshold' => 5, 'image_path' => 'catalog/SEMBAKO-01.png'],
            ['sku' => 'SEMBAKO-02', 'barcode' => '8990001000073', 'name' => 'Minyak Goreng 1L', 'category' => 'Sembako', 'price' => 18500, 'cost' => 15200, 'stock' => 2, 'stock_alert_threshold' => 6, 'image_path' => 'catalog/SEMBAKO-02.png'],
            ['sku' => 'SEMBAKO-03', 'barcode' => '8990001000080', 'name' => 'Telur Ayam (1 kg)', 'category' => 'Sembako', 'price' => 29000, 'cost' => 25000, 'stock' => 30, 'stock_alert_threshold' => 8, 'image_path' => 'catalog/SEMBAKO-03.png'],
        ];

        foreach ($products as $product) {
            Product::updateOrCreate(['sku' => $product['sku']], $product + ['is_active' => true]);
        }
    }
}
