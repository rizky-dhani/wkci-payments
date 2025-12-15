<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SaveRevenueResponseResource extends JsonResource
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
            'RevenueBatchId' => $this->revenueBatchId ?? null,
            'transactionReturnDatas' => $this->transactionReturnDatas ?? [],
            'ErrorTransactionNumber' => $this->errorTransactionNumber ?? [],
            'message' => $this->message ?? 'Success',
            'success' => $this->success ?? true,
        ];
    }
}
