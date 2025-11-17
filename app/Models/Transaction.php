<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transactionId',
        'trx_order_no',
        'partner_ref_no',
        'customer_name',
        'customer_email',
        'payment_status',
        'paid_at',
        'amount',
        'qrCode',
        'submitted_date',
    ];

    protected $casts = [
        'submitted_date' => 'datetime',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    /**
     * Scope to get transactions by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope to get pending transactions
     */
    public function scopePending($query)
    {
        return $query->whereNull('payment_status')->orWhere('payment_status', 'pending');
    }

    /**
     * Check if transaction is paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid' && ! is_null($this->paid_at);
    }

    /**
     * Mark transaction as paid
     */
    public function markAsPaid(): void
    {
        $this->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
