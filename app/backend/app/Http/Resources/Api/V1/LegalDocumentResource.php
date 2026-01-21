<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="LegalDocumentResource",
 *   type="object",
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="type", type="string", example="terms"),
 *   @OA\Property(property="title", type="string", example="Términos y Condiciones"),
 *   @OA\Property(property="content", type="string", example="<h1>Términos</h1><p>Contenido...</p>"),
 *   @OA\Property(property="version", type="string", example="v1.0"),
 *   @OA\Property(property="status", type="string", example="active"),
 *   @OA\Property(property="effective_date", type="string", format="date", example="2026-01-21"),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-21T10:00:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-21T10:00:00Z")
 * )
 */
class LegalDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'title' => $this->title,
            'content' => $this->content,
            'version' => $this->version,
            'status' => $this->status,
            'effective_date' => $this->effective_date?->format('Y-m-d'),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
