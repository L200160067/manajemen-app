<?php

namespace App\Models;

use App\Models\InvoiceItem;
use Illuminate\Support\Number;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'current_price',
    ];

    protected $casts = [
        'current_price' => 'decimal:2',
    ];

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getFormattedCurrentPriceAttribute(): string
    {
        return Number::currency($this->current_price, 'IDR', 'id');
    }

    public function scopeSearch($query, string $term)
    {
        $query->where('name', 'like', "%$term%");
    }
}
