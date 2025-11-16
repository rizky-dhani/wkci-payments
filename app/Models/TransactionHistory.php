<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'transaction_time',
        'amount',
        'remarks',
        'revenue_batch_id',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:4',
    ];

    /**
     * Generate a unique transaction number in the format WKCI-Ymd-(8 digit auto increment)
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->transaction_number)) {
                $model->transaction_number = $model->generateUniqueTransactionNumber();
            }
        });
    }

    /**
     * Generate a unique transaction number
     */
    public function generateUniqueTransactionNumber(): string
    {
        $date = now()->format('Ymd');

        // Get the highest transaction number for today
        $lastTransaction = self::where('transaction_number', 'LIKE', "WKCI-{$date}-%")
            ->orderByRaw('CAST(RIGHT(transaction_number, 8) AS UNSIGNED) DESC')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->transaction_number, -8);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return "WKCI-{$date}-".str_pad($newNumber, 8, '0', STR_PAD_LEFT);
    }
}
