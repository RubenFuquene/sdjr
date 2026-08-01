<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\Product;
use App\Models\ProductCommerceBranch;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-277 Fase 1, Tarea 3.2: publica o despublica un producto en una sola
 * sede sin exigir reenviar el producto completo (a diferencia de Store/Update,
 * que reemplazan la asignación de sedes entera vía commerce_branches[]).
 *
 * @OA\Schema(
 *   schema="UpdateProductBranchPublicationRequest",
 *   required={"is_published"},
 *
 *   @OA\Property(property="is_published", type="boolean", example=true, description="Whether the product should be visible to customers in this branch. Requires quantity_available > 0; packages cannot be published yet (SCRUM-277 Fase 1, Opción A).")
 * )
 */
class UpdateProductBranchPublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->can('provider.products.update')) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        $product = Product::find($this->route('id'));

        if (! $product) {
            return false;
        }

        return Commerce::query()
            ->where('id', $product->commerce_id)
            ->where('owner_user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'is_published' => ['required', 'boolean'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $pivot = ProductCommerceBranch::query()
                ->where('product_id', $this->route('id'))
                ->where('commerce_branch_id', $this->route('branchId'))
                ->first();

            if (! $pivot) {
                $validator->errors()->add(
                    'commerce_branch_id',
                    'This product is not assigned to the given branch.'
                );

                return;
            }

            if (! $this->boolean('is_published')) {
                return;
            }

            $product = Product::find($this->route('id'));

            // SCRUM-277 Fase 1 (Opción A): los packs no se pueden publicar en
            // ninguna sede todavía. Mismo guard que Store/UpdateProductRequest
            // (Tarea 3.8) — duplicado aquí porque este endpoint no reutiliza el
            // validador de esos FormRequest (payload y ruta distintos).
            if ($product?->product_type === Constant::PRODUCT_TYPE_PACKAGE) {
                $validator->errors()->add('is_published', 'Packages cannot be published yet.');

                return;
            }

            if ($pivot->quantity_available === 0) {
                $validator->errors()->add(
                    'is_published',
                    'Cannot publish a branch with zero available quantity.'
                );
            }
        });
    }
}
