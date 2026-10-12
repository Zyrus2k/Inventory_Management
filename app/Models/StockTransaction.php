<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class StockTransaction extends Model
{
    protected $fillable = [
        'reference', 'product_id', 'user_id', 'supplier_id', 'type', 'quantity',
        'previous_quantity', 'new_quantity', 'transaction_date', 'invoice_no',
        'reason', 'notes',
    ];

    protected $casts = ['transaction_date' => 'date'];

    /** Transactions are an audit trail: they can never be edited or deleted. */
    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Stock transactions cannot be modified.'));
        static::deleting(fn () => throw new LogicException('Stock transactions cannot be deleted.'));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }
}
