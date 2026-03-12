<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreCommerceCommentRequest",
 *     required={"comment", "priority_type_id", "comment_type"},
 *
 *     @OA\Property(property="comment", type="string", maxLength=500, example="Comentario de prueba"),
 *     @OA\Property(property="priority_type_id", type="integer", example=1, description="Prioridad del comentario, 1 para alta, 2 para media, 3 para baja"),
 *     @OA\Property(property="comment_type", type="string", enum={"PR", "SU", "IN", "VA"}, example="SU", description="Tipo de comentario: Producto, Soporte, Información, Validación"),
 *     @OA\Property(property="color", type="string", maxLength=20, example="red"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="Status del comentario, 1 para activo, 0 para inactivo"),
 *     @OA\Property(property="created_by", type="integer", example=1, description="Opcional - ID del usuario que crea el comentario")
 * )
 */
class StoreCommerceCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.comments.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:500'],
            'priority_type_id' => ['required', 'integer', 'exists:priority_types,id'],
            'comment_type' => ['required', 'string', 'in:'.Constant::COMMENT_TYPE_PRODUCT.','.Constant::COMMENT_TYPE_SUPPORT.','.Constant::COMMENT_TYPE_INFO.','.Constant::COMMENT_TYPE_VALIDATION],
            'color' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:1', 'in: '.Constant::STATUS_ACTIVE.','.Constant::STATUS_INACTIVE],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }
}
