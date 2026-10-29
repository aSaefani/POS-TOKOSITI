<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'cashier_id', 'subtotal', 'discount', 'total',
        'amount_paid', 'change_amount', 'payment_method', 'status',
        'void_reason', 'transacted_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
            'amount_paid' => 'integer',
            'change_amount' => 'integer',
            'payment_method' => PaymentMethod::class,
            'status' => TransactionStatus::class,
            'transacted_at' => 'datetime',
        ];
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', TransactionStatus::Completed);
    }

    public function scopeForCashier($query, int $userId)
    {
        return $query->where('cashier_id', $userId);
    }
}
