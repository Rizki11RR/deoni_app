<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['product_id', 'uom_id', 'name', 'quantity', 'store_price', 'hotel_price', 'is_default', 'is_active'])]
class ProductPackaging extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return ['quantity' => 'decimal:6', 'store_price' => 'decimal:2', 'hotel_price' => 'decimal:2', 'is_default' => 'boolean', 'is_active' => 'boolean'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }
}
