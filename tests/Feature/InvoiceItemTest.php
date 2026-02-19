<?php

use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_item_updates_invoice_total(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['total_amount' => 0]);
        $product = Product::factory()->create(['current_price' => 50000]);

        $response = $this->actingAs($user)->post(route('invoices.items.store', $invoice), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect();
        
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $invoice->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50000,
            'subtotal' => 100000,
        ]);

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'total_amount' => 100000,
        ]);
    }

    public function test_deleting_item_updates_invoice_total(): void
    {
        $user = User::factory()->create();
        $invoice = Invoice::factory()->create(['total_amount' => 100000]);
        $product = Product::factory()->create(['current_price' => 50000]);
        
        $item = $invoice->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 50000,
            'subtotal' => 100000,
        ]);

        $response = $this->actingAs($user)->delete(route('items.destroy', $item));

        $response->assertRedirect();
        
        $this->assertDatabaseMissing('invoice_items', ['id' => $item->id]);
        
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'total_amount' => 0,
        ]);
    }
}
