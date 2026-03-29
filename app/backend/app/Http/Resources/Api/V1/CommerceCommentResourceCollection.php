<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * CommerceCommentResourceCollection
 */
class CommerceCommentResourceCollection extends ResourceCollection
    /**
     * @OA\Schema(
     *     schema="CommerceCommentResourceCollection",
     *
     *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommerceCommentResource"))
     * )
     */
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
        ];
    }
}
