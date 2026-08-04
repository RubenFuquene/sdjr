<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\Product;
use App\Models\ProductCommerceBranch;
use App\Services\PackageAvailabilityCalculator;
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
 *   @OA\Property(property="is_published", type="boolean", example=true, description="Whether the product should be visible to customers in this branch. Requires quantity_available > 0; for packages also requires enough component stock in this branch (SCRUM-361).")
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

            if ($pivot->quantity_available === 0) {
                $validator->errors()->add(
                    'is_published',
                    'Cannot publish a branch with zero available quantity.'
                );

                return;
            }

            $product = Product::find($this->route('id'));

            if ($product?->product_type === Constant::PRODUCT_TYPE_PACKAGE) {
                $this->validatePackageCapacity($validator, $product, (int) $this->route('branchId'), (int) $pivot->quantity_available);
            }
        });
    }

    /**
     * SCRUM-361, Tarea 5.3: un pack no se publica en una sede si sus
     * componentes ya no alcanzan para armar, al menos, el compromiso
     * actual. Este endpoint no cambia quantity_available (solo el estado de
     * publicación), así que se compara el compromiso ya guardado contra la
     * capacidad real de los componentes en esa sede.
     */
    private function validatePackageCapacity(Validator $validator, Product $package, int $branchId, int $committed): void
    {
        $package->loadMissing('packageItems');

        $packageItems = $package->packageItems->map(fn ($item) => [
            'product' => $item,
            'quantity' => (int) $item->pivot->quantity,
        ]);

        $maxPacks = app(PackageAvailabilityCalculator::class)->maxPackageQuantity($packageItems, $branchId, $package->id);

        if ($committed > $maxPacks) {
            $validator->errors()->add(
                'is_published',
                "Cannot publish: components in this branch only support {$maxPacks} pack(s), but {$committed} are committed."
            );
        }
    }
}
