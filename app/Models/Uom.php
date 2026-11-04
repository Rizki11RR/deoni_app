<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['uom_category_id', 'name', 'symbol', 'conversion_factor', 'conversion_type', 'precision', 'is_base', 'is_active'])]
class Uom extends Model
{
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return ['conversion_factor' => 'decimal:6', 'precision' => 'integer', 'is_base' => 'boolean', 'is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(UomCategory::class, 'uom_category_id');
    }

    public function productPackagings(): HasMany
    {
        return $this->hasMany(ProductPackaging::class);
    }
}
