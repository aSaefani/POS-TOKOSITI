# POS TOKO SITI

Sistem Point of Sale untuk operasional toko ritel, dibangun dengan Laravel 12, Blade, Alpine.js, Tailwind CSS, dan Chart.js. Aplikasi mencakup kasir, manajemen produk dan stok, dashboard analitik, serta laporan penjualan untuk audit.

## Fitur utama

- POS dengan pencarian produk, keranjang, pembayaran, dan stok yang diperbarui setelah checkout.
- Dashboard dengan grafik penjualan minggu ini, bulan ini, dan tahun ini.
- Ekspor laporan penjualan berdasarkan rentang tanggal ke CSV UTF-8 yang dapat dibuka di Microsoft Excel. Isi laporan berupa rincian item dan informasi transaksi; nilai total transaksi hanya diisi sekali per invoice agar tidak terhitung ganda.
- Katalog delapan produk demo dengan foto hasil generator gambar dan logo TOKO SITI.
- Hak akses berbasis peran untuk Admin, Owner, dan Kasir.

## File yang disertakan dalam repository

| Lokasi | Isi |
|---|---|
| `app/`, `routes/`, `bootstrap/`, `config/` | Logika Laravel, controller, service, middleware, model, dan rute aplikasi. |
| `resources/` | Template halaman, CSS, dan JavaScript untuk dashboard, POS, transaksi, serta pengelolaan produk/pengguna. |
| `database/migrations/`, `database/factories/`, `database/seeders/` | Struktur tabel dan data demo untuk akun serta produk. |
| `public/images/` | Logo toko, gambar pengganti, dan delapan foto produk katalog. |
| `tests/` | Pengujian fitur aplikasi. |
| `composer.json`, `composer.lock`, `package.json`, `package-lock.json` | Daftar dan versi dependensi PHP serta JavaScript. |
| `.env.example`, `README.md` | Contoh konfigurasi tanpa kredensial dan dokumentasi setup. |

File lokal yang berisi rahasia atau data runtime tidak disertakan: `.env`, database SQLite (`database/*.sqlite`), `vendor/`, `node_modules/`, log, dan hasil build `public/build/`. Dependensi dan hasil build dibuat kembali saat instalasi.

Sistem Point of Sale berbasis web untuk TOKO SITI. Dibangun dengan Laravel 12 (backend & RBAC), Blade + Alpine.js (interaktivitas), Tailwind CSS v4 (styling), dan Chart.js (grafik). Tampilan memakai gaya glassmorphism dengan tema terang.

## Struktur proyek

```
toko-siti/
├── app/
│   ├── Enums/                 Role, PaymentMethod, TransactionStatus (+ matriks izin Role)
│   ├── Http/
│   │   ├── Controllers/       Tipis: hanya orkestrasi request → service → view/JSON
│   │   │   ├── Auth/AuthController.php
│   │   │   ├── Pos/PosController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProductController.php · UserController.php · TransactionController.php
│   │   ├── Middleware/
│   │   │   ├── CheckPermission.php    RBAC terpusat: ->middleware('permission:…')
│   │   │   └── SecurityHeaders.php    CSP, X-Frame-Options, dll.
│   │   └── Requests/          Validasi & otorisasi input (FormRequest)
│   ├── Models/                User, Product, Transaction, TransactionDetail
│   ├── Services/
│   │   ├── CheckoutService.php   Checkout & void dalam DB transaction + lockForUpdate
│   │   └── DashboardService.php  Agregasi ringkasan, tren mingguan/bulanan/tahunan, peringatan stok
│   └── Providers/AppServiceProvider.php   Rate limiter (login, checkout)
├── database/
│   ├── migrations/            Skema: users, products, transactions, transaction_details
│   ├── factories/ · seeders/  Data demo (3 akun + 8 produk)
├── resources/
│   ├── css/app.css            Token desain, utilitas kaca (glass), animasi, skeleton
│   ├── js/
│   │   ├── app.js             Bootstrap Alpine dan Axios (CSRF)
│   │   ├── pos.js             State keranjang & checkout kasir
│   │   ├── dashboard.js       Grafik Chart.js & polling aktivitas
│   │   └── lib/money.js       Format Rupiah (integer, tanpa float)
│   └── views/
│       ├── layouts/           base (head, flash) · app (sidebar berbasis izin)
│       ├── components/        stat-card, badge
│       ├── auth/ · dashboard/ · pos/ · transactions/ · products/ · users/
├── routes/web.php             Rute dikelompokkan per permission
└── tests/Feature/PosAccessAndCheckoutTest.php
```

## Hak akses (RBAC)

Sumber kebenaran tunggal ada di `App\Enums\Role::permissions()`. Pengecekan dilakukan di tiga lapis:

1. **Backend route** — `permission:…` middleware pada setiap grup rute.
2. **Backend logika** — `hasPermission()` di controller/service (misal kasir hanya melihat `cashier_id` miliknya).
3. **Frontend** — sidebar dan tombol dirender dari izin yang sama. Ini hanya untuk UX; keamanan tetap ditegakkan di backend.

| Fitur | Admin | Owner | Kasir |
|---|:-:|:-:|:-:|
| Dashboard & grafik | ✓ | ✓ (read-only) | — |
| Kasir / POS | ✓ | — | ✓ |
| Riwayat transaksi | Semua | Semua | Miliknya |
| Batalkan transaksi (soft void + stok kembali) | ✓ | — | — |
| Kelola produk | ✓ | — | — |
| Kelola pengguna | ✓ | — | — |

Owner tidak punya rute hapus/ubah untuk transaksi. Pembatalan transaksi hanya status `voided`, data tidak dihapus.

## Keamanan

- **CSRF**: aktif untuk seluruh form; request AJAX membawa `X-XSRF-TOKEN` dari cookie Laravel.
- **SQL Injection**: seluruh query memakai Eloquent/parameter binding. Tidak ada raw input di SQL.
- **XSS**: Blade men-escape output `{{ }}`; Alpine memakai `x-text` (bukan `x-html`). Header CSP dibatasi ke sumber sendiri.
- **Password**: di-hash dengan bcrypt (`password => hashed`). Aturan: min. 10 karakter, huruf & angka.
- **Rate limiting**: login 5 percobaan gagal per email+IP per menit, plus batas 10/menit per IP. Checkout 60/menit per kasir.
- **Session**: regenerasi ID saat login, invalidasi saat logout, cookie `HttpOnly` + `SameSite=Lax`.
- **Integritas harga**: klien hanya mengirim `product_id` dan `quantity`. Harga dan total dihitung ulang di server dari katalog.
- **Konkurensi stok**: `lockForUpdate()` di dalam DB transaction mencegah stok minus saat dua kasir checkout bersamaan.
- **Uang**: seluruh nominal disimpan sebagai `BIGINT` rupiah utuh, tidak ada float.

## Setup

Syarat: PHP 8.2+, Composer, Node 20+.

```bash
git clone https://github.com/aSaefani/POS-TOKOSITI.git toko-siti
cd toko-siti

composer install
npm install
cp .env.example .env    # atau buat .env manual, lihat contoh di bawah
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm run dev            # terminal 1
php artisan serve      # terminal 2
```

Isi `.env` minimal:

```env
APP_NAME="POS TOKO SITI"
APP_LOCALE=id
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=id_ID
DB_CONNECTION=sqlite            # atau mysql untuk produksi
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_SECURE_COOKIE=false     # true saat HTTPS
```

Akun demo (seeder, **ganti sebelum dipakai di produksi**):

| Email | Peran | Kata sandi |
|---|---|---|
| admin@tokositi.test | Admin | `ChangeMe2026!` |
| owner@tokositi.test | Owner | `ChangeMe2026!` |
| kasir@tokositi.test | Kasir | `ChangeMe2026!` |

Menjalankan test:

```bash
php artisan test
```

## Pintasan kasir

| Tombol | Fungsi |
|---|---|
| `/` | Fokus ke kolom pencarian |
| `Enter` di pencarian | Jika cocok tepat dengan barcode/SKU, langsung masuk keranjang (mode scanner) |
| `F9` | Bayar |

## Catatan

- Migrasi `database/migrations/*` memakai timestamp `2026_01_01_*`. Jika Anda sudah punya migrasi lain, pastikan urutannya tetap sesudah `create_users_table` karena `transactions` mereferensi `users`.
- Grafik tren dan aktivitas diperbarui otomatis setiap 20 detik. Polling berhenti saat tab tidak terlihat.
- Untuk produksi: aktifkan HTTPS, set `APP_DEBUG=false`, jalankan `php artisan config:cache route:cache view:cache`, dan gunakan MySQL atau MariaDB.
