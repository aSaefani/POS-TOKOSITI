<?php

namespace App\Enums;

use App\Support\Concerns\HasEnumValues;

enum TransactionStatus: string
{
    use HasEnumValues;

    case Completed = 'completed';
    case Voided = 'voided';
}
