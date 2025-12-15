<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetVoidResponseResource extends JsonResource
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
            'data' => $this->data ?? [],
            'message' => $this->message ?? 'Success',
            'success' => $this->success ?? true,
        ];
    }
}
