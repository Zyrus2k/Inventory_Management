<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'admin'): User
    {
        return User::create([
            'name' => ucfirst($role), 'email' => "{$role}@test.com",
            'password' => 'Password123', 'role' => $role, 'is_active' => true,
        ]);
    }

    private function product(array $o = []): Product
    {
        $cat = Category::firstOrCreate(['name' => 'General']);

        return Product::create($o + [
            'name' => 'Widget', 'sku' => 'SKU-'.uniqid(), 'category_id' => $cat->id,
            'purchase_price' => 10, 'selling_price' => 15, 'quantity' => 10, 'min_stock' => 5, 'unit' => 'pcs',
        ]);
    }

    public function test_login_and_logout(): void
    {
        $this->user();
        $this->post('/login', ['email' => 'admin@test.com', 'password' => 'Password123'])->assertRedirect('/');
        $this->assertAuthenticated();
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->user();
        $this->post('/login', ['email' => 'admin@test.com', 'password' => 'nope'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_stock_in_increases_quantity_and_logs_transaction(): void
    {
        $p = $this->product();
        $this->actingAs($this->user())->post('/stock-in', [
            'product_id' => $p->id, 'quantity' => 5, 'transaction_date' => now()->toDateString(),
        ])->assertRedirect(route('transactions.index'));

        $this->assertSame(15, $p->fresh()->quantity);
        $this->assertDatabaseHas('stock_transactions', ['product_id' => $p->id, 'type' => 'in', 'previous_quantity' => 10, 'new_quantity' => 15]);
    }

    public function test_stock_out_decreases_quantity(): void
    {
        $p = $this->product();
        $this->actingAs($this->user('staff'))->post('/stock-out', [
            'product_id' => $p->id, 'quantity' => 4, 'reason' => 'Sale', 'transaction_date' => now()->toDateString(),
        ]);

        $this->assertSame(6, $p->fresh()->quantity);
    }

    public function test_stock_out_cannot_exceed_available_stock(): void
    {
        $p = $this->product();
        $this->actingAs($this->user())->post('/stock-out', [
            'product_id' => $p->id, 'quantity' => 50, 'reason' => 'Sale', 'transaction_date' => now()->toDateString(),
        ])->assertSessionHasErrors('quantity');

        $this->assertSame(10, $p->fresh()->quantity);
        $this->assertSame(0, StockTransaction::count());
    }

    public function test_invalid_quantity_is_rejected(): void
    {
        $p = $this->product();
        $this->actingAs($this->user())->post('/stock-in', [
            'product_id' => $p->id, 'quantity' => 0, 'transaction_date' => now()->toDateString(),
        ])->assertSessionHasErrors('quantity');
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $p = $this->product(['sku' => 'DUP-1']);
        $this->actingAs($this->user())->post('/products', [
            'name' => 'Other', 'sku' => 'DUP-1', 'category_id' => $p->category_id,
            'purchase_price' => 1, 'selling_price' => 2, 'min_stock' => 1, 'unit' => 'pcs',
        ])->assertSessionHasErrors('sku');
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $p = $this->product();
        $this->actingAs($this->user())->delete(route('categories.destroy', $p->category_id))->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $p->category_id]);
    }

    public function test_product_with_history_cannot_be_deleted(): void
    {
        $admin = $this->user();
        $p = $this->product();
        $this->actingAs($admin)->post('/stock-in', ['product_id' => $p->id, 'quantity' => 1, 'transaction_date' => now()->toDateString()]);
        $this->delete(route('products.destroy', $p))->assertSessionHas('error');
        $this->assertDatabaseHas('products', ['id' => $p->id]);
    }

    public function test_staff_cannot_open_admin_pages(): void
    {
        $this->actingAs($this->user('staff'));
        foreach (['/users', '/categories', '/reports', '/products/create', '/suppliers/create'] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->get('/products')->assertOk();
        $this->get('/transactions')->assertOk();
    }

    public function test_low_stock_page_lists_low_and_out_products(): void
    {
        $this->product(['name' => 'LowItem', 'quantity' => 3, 'min_stock' => 5]);
        $this->product(['name' => 'ZeroItem', 'quantity' => 0, 'min_stock' => 5]);
        $this->product(['name' => 'FineItem', 'quantity' => 50, 'min_stock' => 5]);

        $this->actingAs($this->user())->get('/low-stock')
            ->assertSee('LowItem')->assertSee('ZeroItem')->assertDontSee('FineItem');
    }

    public function test_transactions_are_immutable(): void
    {
        $p = $this->product();
        $this->actingAs($this->user())->post('/stock-in', ['product_id' => $p->id, 'quantity' => 1, 'transaction_date' => now()->toDateString()]);
        $this->expectException(\LogicException::class);
        StockTransaction::first()->delete();
    }

    public function test_admin_cannot_change_own_role(): void
    {
        $admin = $this->user();
        $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => 'Admin', 'email' => 'admin@test.com', 'role' => 'staff',
        ]);
        $this->assertSame('admin', $admin->fresh()->role);
    }
}
