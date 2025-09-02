<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'invoice_number',
        'status',
        'subtotal',
        'discount',
        'tax',
        'shipping_fee',
        'total',
        'issue_date',
        'due_date',
        'paid_at',
        'receipt_number',
        'receipt_at',
        'cancelled_at',
    ];

    protected $cast = [
        'status' => InvoiceStatus::class,
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
