<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Support\Number;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;
    //
    protected $fillable = [
        'client_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'status',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'status' => InvoiceStatus::class,
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function getFormattedTotalAmountAttribute(): string
    {
        return Number::currency($this->total_amount, 'IDR', 'id');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
            ->where('status', '!=', InvoiceStatus::Paid);
    }

    public function scopeByStatus($query, InvoiceStatus $status)
    {
        return $query->where('status', $status);
    }
}
