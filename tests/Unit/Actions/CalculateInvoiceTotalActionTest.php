<?php

use App\Actions\CalculateInvoiceTotalAction;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculateInvoiceTotalActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_invoice_total_correctly(): void
    {
        $invoice = Invoice::factory()->create(['total_amount' => 0]);
        
        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 2,
            'unit_price' => 50000,
            'subtotal' => 100000,
        ]);

        InvoiceItem::factory()->create([
            'invoice_id' => $invoice->id,
            'quantity' => 1,
            'unit_price' => 25000,
            'subtotal' => 25000,
        ]);

        $action = new CalculateInvoiceTotalAction();
        $updatedInvoice = $action->execute($invoice);

        $this->assertEquals(125000, $updatedInvoice->total_amount);
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'total_amount' => 125000,
        ]);
    }
}
