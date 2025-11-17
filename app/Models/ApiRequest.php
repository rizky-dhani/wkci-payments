<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'endpoint',
        'method',
        'request_headers',
        'request_body',
        'response_status',
        'response_headers',
        'response_body',
        'execution_time',
        'error_message',
    ];

    protected $casts = [
        'request_headers' => 'json',
        'request_body' => 'json',
        'response_headers' => 'json',
        'response_body' => 'json',
        'execution_time' => 'decimal:4',
    ];

    /**
     * Scope to get successful requests
     */
    public function scopeSuccessful($query)
    {
        return $query->where('response_status', '<', 400);
    }

    /**
     * Scope to get failed requests
     */
    public function scopeFailed($query)
    {
        return $query->where('response_status', '>=', 400);
    }

    /**
     * Scope to filter by endpoint
     */
    public function scopeByEndpoint($query, $endpoint)
    {
        return $query->where('endpoint', $endpoint);
    }

    /**
     * Scope to filter by HTTP method
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }
}
