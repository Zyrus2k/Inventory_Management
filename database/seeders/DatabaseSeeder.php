<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(StockService $stock): void
    {
        // Demo accounts. Set ADMIN_PASSWORD / STAFF_PASSWORD in .env to override.
        $admin = User::updateOrCreate(['email' => 'admin@example.com'], [
            'name' => 'System Administrator',
            'password' => env('ADMIN_PASSWORD', 'Admin#12345'),
            'role' => 'admin', 'is_active' => true,
        ]);
        User::updateOrCreate(['email' => 'staff@example.com'], [
            'name' => 'Demo Staff',
            'password' => env('STAFF_PASSWORD', 'Staff#12345'),
            'role' => 'staff', 'is_active' => true,
        ]);

        if (Product::exists()) {
            return; // don't duplicate demo data on re-seed
        }

        $cats = collect(['Electronics', 'Office Supplies', 'Groceries', 'Hardware', 'Cleaning'])
            ->mapWithKeys(fn ($n) => [$n => Category::firstOrCreate(['name' => $n], ['description' => "{$n} items"])]);

        $sup = [
            Supplier::create(['name' => 'TechSource Trading', 'contact_person' => 'Maria Santos', 'phone' => '0917-123-4567', 'email' => 'sales@techsource.test', 'address' => 'Quezon City, Metro Manila']),
            Supplier::create(['name' => 'PaperWorld Inc.', 'contact_person' => 'John Reyes', 'phone' => '0928-555-0101', 'email' => 'orders@paperworld.test', 'address' => 'Makati City']),
            Supplier::create(['name' => 'FreshMart Distributors', 'contact_person' => 'Ana Cruz', 'phone' => '0999-222-3344', 'email' => 'ana@freshmart.test', 'address' => 'Calamba, Laguna']),
        ];

        // name, sku, category, supplier idx, buy, sell, min, unit, stock-in, stock-out
        $items = [
            ['Wireless Mouse', 'ELC-001', 'Electronics', 0, 250, 399, 10, 'pcs', 40, 12],
            ['USB-C Cable 1m', 'ELC-002', 'Electronics', 0, 80, 150, 20, 'pcs', 60, 45],
            ['Mechanical Keyboard', 'ELC-003', 'Electronics', 0, 1200, 1899, 5, 'pcs', 15, 15],
            ['A4 Bond Paper (ream)', 'OFF-001', 'Office Supplies', 1, 220, 290, 25, 'ream', 100, 30],
            ['Ballpoint Pen (box of 12)', 'OFF-002', 'Office Supplies', 1, 60, 95, 15, 'box', 50, 38],
            ['Stapler', 'OFF-003', 'Office Supplies', 1, 90, 140, 8, 'pcs', 20, 5],
            ['Rice 25kg', 'GRO-001', 'Groceries', 2, 1100, 1350, 10, 'sack', 30, 22],
            ['Cooking Oil 1L', 'GRO-002', 'Groceries', 2, 85, 110, 24, 'btl', 72, 40],
            ['Claw Hammer', 'HRD-001', 'Hardware', 0, 180, 275, 6, 'pcs', 12, 4],
            ['Screwdriver Set', 'HRD-002', 'Hardware', 0, 320, 480, 6, 'set', 10, 5],
            ['Dishwashing Liquid 500ml', 'CLN-001', 'Cleaning', 2, 45, 65, 20, 'btl', 48, 30],
            ['Bleach 1L', 'CLN-002', 'Cleaning', 2, 38, 55, 15, 'btl', 30, 8],
        ];

        foreach ($items as [$name, $sku, $cat, $s, $buy, $sell, $min, $unit, $in, $out]) {
            $p = Product::create([
                'name' => $name, 'sku' => $sku, 'barcode' => '4800'.str_pad((string) crc32($sku) % 100000000, 8, '0', STR_PAD_LEFT),
                'category_id' => $cats[$cat]->id, 'supplier_id' => $sup[$s]->id,
                'purchase_price' => $buy, 'selling_price' => $sell, 'quantity' => 0, 'min_stock' => $min, 'unit' => $unit,
            ]);

            $stock->record($p->id, 'in', $in, [
                'transaction_date' => now()->subDays(6)->toDateString(), 'supplier_id' => $sup[$s]->id,
                'invoice_no' => 'INV-'.rand(1000, 9999), 'notes' => 'Initial delivery',
            ], $admin->id);

            $stock->record($p->id, 'out', $out, [
                'transaction_date' => now()->subDays(rand(0, 4))->toDateString(), 'reason' => 'Sale',
            ], $admin->id);
        }
    }
}
