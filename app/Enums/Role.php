<?php

namespace App\Enums;

use App\Support\Concerns\HasEnumValues;

enum Role: string
{
    use HasEnumValues;

    case Admin = 'admin';
    case Owner = 'owner';
    case Kasir = 'kasir';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Owner => 'Owner',
            self::Kasir => 'Kasir',
        };
    }

    /**
     * Matriks izin tunggal. Semua pengecekan (middleware, policy, Blade, route frontend)
     * membaca dari sini agar tidak ada aturan yang tersebar.
     */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => [
                'dashboard.view', 'reports.view', 'analytics.view', 'cashier.performance',
                'products.manage', 'users.manage', 'settings.manage',
                'transactions.view_all', 'transactions.void', 'pos.use', 'transactions.view_own',
            ],
            self::Owner => [
                'dashboard.view', 'reports.view', 'analytics.view', 'cashier.performance',
                'transactions.view_all',
            ],
            self::Kasir => [
                'pos.use', 'transactions.view_own',
            ],
        };
    }

    public function can(string $permission): bool
    {
        return in_array($permission, $this->permissions(), true);
    }

    /** Beranda setelah login, sesuai peran. */
    public function homeRoute(): string
    {
        return $this === self::Kasir ? 'pos.index' : 'dashboard';
    }
}
