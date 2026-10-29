<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'sku', 'barcode', 'name', 'category', 'price', 'cost',
        'stock', 'stock_alert_threshold', 'image_path', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'cost' => 'integer',
            'stock' => 'integer',
            'stock_alert_threshold' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected $appends = ['image_url', 'is_low_stock'];

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', $term)
                ->orWhere('barcode', $term);
        });
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock', '<=', 'stock_alert_threshold');
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->stock <= $this->stock_alert_threshold;
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        return str_starts_with($this->image_path, 'catalog/')
            ? asset('images/'.$this->image_path)
            : Storage::disk('public')->url($this->image_path);
    }
}
