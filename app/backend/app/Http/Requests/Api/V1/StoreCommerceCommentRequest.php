<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreCommerceCommentRequest",
 *     required={"comment", "priority_type_id", "comment_type"},
 *
 *     @OA\Property(property="comment", type="string", maxLength=500, example="Comentario de prueba"),
 *     @OA\Property(property="priority_type_id", type="integer", example=1, description="Prioridad del comentario, 1 para alta, 2 para media, 3 para baja"),
 *     @OA\Property(property="comment_type", type="string", enum={"PR", "SU", "IN", "VA", "RJ"}, example="SU", description="Tipo de comentario: Producto, Soporte, Información, Validación, Rechazo"),
 *     @OA\Property(property="color", type="string", maxLength=20, example="red"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="Status del comentario, 1 para activo, 0 para inactivo"),
 *     @OA\Property(property="created_by", type="integer", example=1, description="Opcional - ID del usuario que crea el comentario")
 * )
 */
class StoreCommerceCommentRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        return ($this->user()?->can('provider.comments.create') ?? false)
            && $this->userCanAccessCommerce();
    }

    public function rules(): array
    {
        return [
            'comment' => ['required', 'string', 'max:500'],
            'priority_type_id' => ['required', 'integer', 'exists:priority_types,id'],
            'comment_type' => ['required', 'string', 'in:'.implode(',', array_keys(Constant::COMMENT_TYPE_ARRAY))],
            'color' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string', 'max:1', 'in: '.Constant::STATUS_ACTIVE.','.Constant::STATUS_INACTIVE],
            'created_by' => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    /**
     * Reglas de dominio adicionales:
     * - Un no-admin (proveedor dueño) solo puede crear tipos permitidos (MS).
     * - Los mensajes (MS) solo se aceptan mientras el comercio está en ruta de
     *   aprobación; el canal se cierra al aprobar/rechazar.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $type = (string) $this->input('comment_type');
            $isAdmin = $this->user()?->hasAnyRole(['superadmin', 'admin']) ?? false;

            if (! $isAdmin && ! in_array($type, Constant::PROVIDER_CREATABLE_COMMENT_TYPES, true)) {
                $validator->errors()->add('comment_type', 'No autorizado para crear este tipo de comentario.');

                return;
            }

            if ($type === Constant::COMMENT_TYPE_MESSAGE && ! $this->commerceAcceptsMessages()) {
                $validator->errors()->add('comment_type', 'El canal de mensajes no está disponible para el estado actual del comercio.');
            }
        });
    }

    /**
     * Whether the target commerce is in an approval-route state that accepts messages.
     */
    private function commerceAcceptsMessages(): bool
    {
        $state = Commerce::query()->whereKey($this->resolveCommerceId())->value('is_verified');

        return $state !== null
            && in_array((int) $state, Constant::COMMERCE_MESSAGING_STATES, true);
    }
}
