<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['product_id', 'physical_quantity', 'reserved_quantity', 'is_verified'])]
class StockBalance extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return ['physical_quantity' => 'decimal:3', 'reserved_quantity' => 'decimal:3', 'is_verified' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvailableQuantityAttribute(): string
    {
        return number_format((float) $this->physical_quantity - (float) $this->reserved_quantity, 3, '.', '');
    }
}
