<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="CommercePayoutMethodResource",
 *   type="object",
 *
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="commerce_id", type="integer", example=1),
 *   @OA\Property(property="type", type="string", example="bank"),
 *   @OA\Property(property="bank", ref="#/components/schemas/BankResource"),
 *   @OA\Property(property="account_type", type="string", example="savings"),
 *   @OA\Property(property="account_number", type="string", example="1234567890"),
 *   @OA\Property(property="owner", ref="#/components/schemas/UserResource"),
 *   @OA\Property(property="is_primary", type="boolean", example=true),
 *   @OA\Property(property="status", type="string", example="1"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-21T10:00:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-21T10:00:00Z")
 * )
 */
class CommercePayoutMethodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
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
