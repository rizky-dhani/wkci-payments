<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GetSummaryResponseResource extends JsonResource
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
            'Months' => $this->months ?? null,
            'Years' => $this->year ?? null,
            'data' => $this->data ?? [],
            'message' => $this->message ?? 'Success',
            'success' => $this->success ?? true,
        ];
    }
}
