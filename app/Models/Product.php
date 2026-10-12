<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name', 'sku', 'barcode', 'description', 'category_id', 'supplier_id',
        'purchase_price', 'selling_price', 'quantity', 'min_stock', 'unit',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'quantity' => 'integer',
        'min_stock' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class);
    }

    /** Stock is zero. */
    public function scopeOut(Builder $q): Builder
    {
        return $q->where('quantity', 0);
    }

    /** Stock is above zero but at or below the minimum threshold. */
    public function scopeLow(Builder $q): Builder
    {
        return $q->where('quantity', '>', 0)->whereColumn('quantity', '<=', 'min_stock');
    }

    /** Low OR out of stock. */
    public function scopeNeedsRestock(Builder $q): Builder
    {
        return $q->whereColumn('quantity', '<=', 'min_stock');
    }

    public function scopeInStock(Builder $q): Builder
    {
        return $q->whereColumn('quantity', '>', 'min_stock');
    }

    /** out | low | ok */
    public function getStatusAttribute(): string
    {
        if ($this->quantity <= 0) {
            return 'out';
        }

        return $this->quantity <= $this->min_stock ? 'low' : 'ok';
    }
}
