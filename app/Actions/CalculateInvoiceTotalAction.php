<?php

namespace App\Actions;

use App\Models\Invoice;

class CalculateInvoiceTotalAction
{
    public function execute(Invoice $invoice): Invoice
    {
        $total = $invoice->items()->sum('subtotal');

        $invoice->update([
            'total_amount' => $total,
        ]);

        return $invoice;
    }
}
