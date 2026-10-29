<?php

namespace App\Enums;

use App\Support\Concerns\HasEnumValues;

enum PaymentMethod: string
{
    use HasEnumValues;

    case Cash = 'cash';
    case Qris = 'qris';
    case Debit = 'debit';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Tunai',
            self::Qris => 'QRIS',
            self::Debit => 'Kartu Debit',
        };
    }
}
