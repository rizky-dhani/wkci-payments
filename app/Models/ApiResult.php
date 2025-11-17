<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiResult extends Model
{
    protected $fillable = [
        'transaction_number',
        'transaction_date',
        'amount',
        'remarks',
        'revenue_batch_id',
        'api_type',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'amount' => 'decimal:4',
    ];

    // This model won't have a table in the database as it's for API results
    public $timestamps = false;
}