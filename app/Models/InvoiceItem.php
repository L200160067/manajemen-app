<?php

namespace App\Models;

use App\Models\Invoice;
use App\Models\Product;
use Illuminate\Support\Number;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'invoice_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }


    public function getFormattedUnitPriceAttribute(): string
    {
        return Number::currency($this->unit_price, 'IDR', 'id');
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return Number::currency($this->subtotal, 'IDR', 'id');
    }
}
