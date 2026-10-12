<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Record a stock movement. The product row is locked, the stock is changed and
     * the transaction is written inside ONE database transaction, so inventory
     * quantity and history can never drift apart.
     *
     * @param  array{transaction_date?:string,supplier_id?:int|null,invoice_no?:string|null,reason?:string|null,notes?:string|null}  $data
     */
    public function record(int $productId, string $type, int $quantity, array $data, int $userId): StockTransaction
    {
        if (! in_array($type, ['in', 'out'], true) || $quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'Quantity must be at least 1.']);
        }

        return DB::transaction(function () use ($productId, $type, $quantity, $data, $userId) {
            $product = Product::whereKey($productId)->lockForUpdate()->firstOrFail();
            $previous = $product->quantity;

            if ($type === 'out' && $quantity > $previous) {
                throw ValidationException::withMessages([
                    'quantity' => "Insufficient stock. Only {$previous} {$product->unit} available.",
                ]);
            }

            $new = $type === 'in' ? $previous + $quantity : $previous - $quantity;
            $product->update(['quantity' => $new]);

            return StockTransaction::create([
                'reference' => $this->reference($type),
                'product_id' => $product->id,
                'user_id' => $userId,
                'supplier_id' => $type === 'in' ? ($data['supplier_id'] ?? null) : null,
                'type' => $type,
                'quantity' => $quantity,
                'previous_quantity' => $previous,
                'new_quantity' => $new,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'invoice_no' => $data['invoice_no'] ?? null,
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    private function reference(string $type): string
    {
        do {
            $ref = ($type === 'in' ? 'SI' : 'SO').'-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        } while (StockTransaction::where('reference', $ref)->exists());

        return $ref;
    }
}
