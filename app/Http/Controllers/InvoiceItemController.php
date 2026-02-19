<?php

namespace App\Http\Controllers;

use App\Actions\CalculateInvoiceTotalAction;
use App\Http\Requests\StoreInvoiceItemRequest;
use App\Http\Requests\UpdateInvoiceItemRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;

class InvoiceItemController extends Controller
{
    public function store(StoreInvoiceItemRequest $request, Invoice $invoice, CalculateInvoiceTotalAction $action)
    {
        $product = Product::findOrFail($request->product_id);

        $invoice->items()->create([
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'unit_price' => $product->current_price, // Snapshot price
            'subtotal' => $request->quantity * $product->current_price, // Initial subtotal
        ]);

        $action->execute($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Item added successfully.');
    }

    public function update(UpdateInvoiceItemRequest $request, InvoiceItem $item, CalculateInvoiceTotalAction $action)
    {
        $item->update([
            'quantity' => $request->quantity,
            'subtotal' => $request->quantity * $item->unit_price,
        ]);

        $action->execute($item->invoice);

        return redirect()->route('invoices.show', $item->invoice)
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(InvoiceItem $item, CalculateInvoiceTotalAction $action)
    {
        $invoice = $item->invoice; // Get invoice before deleting item
        $item->delete();
        $action->execute($invoice);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Item removed successfully.');
    }
}
