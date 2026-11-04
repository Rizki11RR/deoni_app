<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'uom_id', 'purchase_uom_id', 'sales_uom_id', 'name', 'code', 'unit', 'store_price', 'hotel_price', 'is_active'])]
class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'store_price' => 'decimal:2',
            'hotel_price' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class);
    }

    public function purchaseUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'purchase_uom_id');
    }

    public function salesUom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'sales_uom_id');
    }

    public function stockReservations(): HasMany
    {
        return $this->hasMany(StockReservation::class);
    }

    public function packagings(): HasMany
    {
        return $this->hasMany(ProductPackaging::class);
    }

    public function uoms(): HasManyThrough
    {
        return $this->hasManyThrough(Uom::class, ProductPackaging::class);
    }
}
