<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'TransactionNumber' => $this->transactionNumber,
            'TransactionDate' => $this->transactionDate,
            'Amount' => $this->amount,
            'Remarks' => $this->remarks,
        ];
    }
}
