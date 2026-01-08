<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class CommercePayoutMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'commerce_id' => $this->commerce_id,
            'type' => $this->type,
            'bank' => new BankResource($this->bank),
            'account_type' => $this->account_type,
            'account_number' => $this->account_number,
            'owner' => new UserResource($this->owner),
            'is_primary' => $this->is_primary,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
