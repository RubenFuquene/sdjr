<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Constants\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CommerceCommentResource
 *
 * @OA\Schema(
 *     schema="CommerceCommentResource",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="commerce_id", type="integer", example=10),
 *     @OA\Property(property="created_by", type="integer", example=5),
 *     @OA\Property(property="comment", type="string", example="Comentario de prueba"),
 *     @OA\Property(property="priority_type_id", type="integer", example=1),
 *     @OA\Property(property="priority_type", type="object",
 *         @OA\Property(property="code", type="string", example="AL"),
 *         @OA\Property(property="name", type="string", example="Alta")
 *     ),
 *     @OA\Property(
 *         property="comment_type",
 *         type="object",
 *         @OA\Property(property="code", type="string", enum={"SU", "IN", "VA"}, example="SU"),
 *         @OA\Property(property="name", type="string", example="Soporte")
 *     ),
 *     @OA\Property(property="color", type="string", example="red"),
 *     @OA\Property(property="status", type="string", example="1"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-03-09T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-03-09T12:00:00Z")
 * )
 */
class CommerceCommentResource extends JsonResource
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
            'created_by' => $this->created_by,
            'comment' => $this->comment,
            'priority_type_id' => $this->priority_type_id,
            'priority_type' => [
                'code' => $this->priorityType ? $this->priorityType->code : null,
                'name' => $this->priorityType ? $this->priorityType->name : null,
            ],
            'comment_type' => [
                'code' => $this->comment_type,
                'name' => Constant::COMMENT_TYPE_ARRAY[$this->comment_type] ?? null,
            ],
            'color' => $this->color,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
