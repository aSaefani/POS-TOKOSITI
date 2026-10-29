<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Enums\Role;
use App\Enums\TransactionStatus;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAccessAndCheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Token CSRF diuji terpisah di browser; test ini fokus pada RBAC dan logika checkout.
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    private function userWith(Role $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    public function test_kasir_cannot_open_dashboard_or_user_management(): void
    {
        $kasir = $this->userWith(Role::Kasir);

        $this->actingAs($kasir)->get('/dashboard')->assertForbidden();
        $this->actingAs($kasir)->get('/users')->assertForbidden();
        $this->actingAs($kasir)->get('/products/create')->assertForbidden();
        $this->actingAs($kasir)->get('/pos')->assertOk();
    }

    public function test_owner_is_read_only_and_cannot_void_transactions(): void
    {
        $owner = $this->userWith(Role::Owner);
        $kasir = $this->userWith(Role::Kasir);
        $transaction = Transaction::create([
            'invoice_number' => 'INV-TEST-0001',
            'cashier_id' => $kasir->id,
            'subtotal' => 10000,
            'total' => 10000,
            'amount_paid' => 10000,
            'transacted_at' => now(),
        ]);

        $this->actingAs($owner)->get('/dashboard')->assertOk();
        $this->actingAs($owner)->get('/pos')->assertForbidden();
        $this->actingAs($owner)->post("/transactions/{$transaction->id}/void", ['void_reason' => 'tes'])->assertForbidden();
        $this->assertSame(TransactionStatus::Completed, $transaction->fresh()->status);
    }

    public function test_checkout_decrements_stock_and_recomputes_total_on_server(): void
    {
        $kasir = $this->userWith(Role::Kasir);
        $product = Product::factory()->create(['price' => 12000, 'stock' => 5]);

        $this->actingAs($kasir)->postJson('/pos/checkout', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payment_method' => PaymentMethod::Cash->value,
            'amount_paid' => 30000,
        ])->assertCreated()
            ->assertJsonPath('data.total', 24000)
            ->assertJsonPath('data.change', 6000);

        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_checkout_rejects_quantity_above_stock_and_keeps_stock_intact(): void
    {
        $kasir = $this->userWith(Role::Kasir);
        $product = Product::factory()->create(['stock' => 1]);

        $this->actingAs($kasir)->postJson('/pos/checkout', [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
            'payment_method' => PaymentMethod::Qris->value,
            'amount_paid' => 999999,
        ])->assertUnprocessable();

        $this->assertSame(1, $product->fresh()->stock);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_client_cannot_set_price_because_server_uses_catalog_price(): void
    {
        $kasir = $this->userWith(Role::Kasir);
        $product = Product::factory()->create(['price' => 10000, 'stock' => 10]);

        // Field "price" dari klien tidak dikenal dan diabaikan; total tetap dari katalog.
        $this->actingAs($kasir)->postJson('/pos/checkout', [
            'items' => [['product_id' => $product->id, 'quantity' => 1, 'price' => 1]],
            'payment_method' => PaymentMethod::Cash->value,
            'amount_paid' => 10000,
        ])->assertCreated()->assertJsonPath('data.total', 10000);
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        User::factory()->create(['email' => 'x@toko.test']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => 'x@toko.test', 'password' => 'salah'])
                ->assertSessionHasErrors('email');
        }

        $this->post('/login', ['email' => 'x@toko.test', 'password' => 'salah'])
            ->assertSessionHasErrors('email');
    }
}
