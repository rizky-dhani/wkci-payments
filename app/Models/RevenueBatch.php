<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RevenueBatch extends Model
{
    protected $table = 'transaction_histories';
    protected $primaryKey = 'revenue_batch_id';

    protected $fillable = [
        'revenue_batch_id',
    ];

    protected $casts = [
        'revenue_batch_id' => 'integer',
    ];

    public function getRouteKeyName()
    {
        return 'revenue_batch_id';
    }

    /**
     * Scope to get unique revenue batch IDs with summary data
     */
    public function scopeUnique($query)
    {
        return $query->selectRaw('revenue_batch_id, COUNT(*) as transaction_count, MAX(transaction_date) as latest_transaction_date')
            ->whereNotNull('revenue_batch_id')
            ->groupBy('revenue_batch_id')
            ->orderBy('revenue_batch_id', 'desc');
    }

    /**
     * Relationship to get all transaction histories with this revenue batch ID
     */
    public function transactionHistories()
    {
        return $this->hasMany(TransactionHistory::class, 'revenue_batch_id', 'revenue_batch_id');
    }

    /**
     * Override the newQuery method to customize the base query
     */
    protected function newQueryForCollection(array $models)
    {
        return $this->newQuery()->whereIn($this->getKeyName(), $this->getKeysForScan($models));
    }

    /**
     * Set the keys for a save update query.
     */
    protected function setKeysForSaveQuery($query)
    {
        $query->where($this->getKeyName(), '=', $this->getKeyForSaveQuery());

        return $query;
    }

    /**
     * Find a model by its primary key for route binding
     */
    public function resolveRouteBinding($value, $field = null)
    {
        // Since this is a view model, we need to check if the revenue_batch_id exists
        $exists = \App\Models\TransactionHistory::where('revenue_batch_id', $value)->exists();

        if ($exists) {
            // Create a temporary instance with the revenue_batch_id
            $instance = new static();
            $instance->setAttribute($this->getKeyName(), $value);

            // Get basic information about the batch for display
            $batchInfo = \App\Models\TransactionHistory::where('revenue_batch_id', $value)
                ->selectRaw('revenue_batch_id, COUNT(*) as transaction_count, MAX(transaction_date) as latest_transaction_date')
                ->first();

            if ($batchInfo) {
                $instance->setRawAttributes($batchInfo->toArray());
            }

            return $instance;
        }

        return null;
    }
}