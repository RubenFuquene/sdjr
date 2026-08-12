<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Enums\FiscalCode;
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
                    __('products.branch_publication.not_assigned')
                );

                return;
            }

            if (! $this->boolean('is_published')) {
                return;
            }

            $product = Product::find($this->route('id'));

            // SCRUM-362: un producto sin clasificar no se publica — solo
            // aplica a 'single', un pack nunca tiene fiscal_code propio.
            if ($product?->fiscal_code === FiscalCode::PendingReview) {
                $validator->errors()->add(
                    'is_published',
                    __('products.branch_publication.pending_review')
                );

                return;
            }

            if ($pivot->quantity_available === 0) {
                $validator->errors()->add(
                    'is_published',
                    __('products.branch_publication.zero_quantity')
                );

                return;
            }

            if ($product?->product_type === Constant::PRODUCT_TYPE_PACKAGE) {
                if ($this->validatePackageComponentsFiscalStatus($validator, $product)) {
                    return;
                }

                $this->validatePackageCapacity($validator, $product, (int) $this->route('branchId'), (int) $pivot->quantity_available);
            }
        });
    }

    /**
     * SCRUM-362: un pack nunca tiene fiscal_code propio, así que el guard de
     * arriba (que solo mira $product->fiscal_code) nunca se dispara para un
     * pack — sin este chequeo, un pack con un componente otro_verificar se
     * podía publicar igual desde este endpoint (bug real, detectado en
     * producción: StoreProductRequest/UpdateProductRequest sí revisaban
     * package_items, este endpoint dedicado no). Bloquea con el mismo
     * criterio que validatePackageComposition() de esos dos.
     *
     * @return bool true si bloqueó (el caller no debe seguir validando).
     */
    private function validatePackageComponentsFiscalStatus(Validator $validator, Product $package): bool
    {
        $package->loadMissing('packageItems');

        $pendingComponent = $package->packageItems->first(
            fn (Product $item) => $item->fiscal_code === FiscalCode::PendingReview
        );

        if (! $pendingComponent) {
            return false;
        }

        $validator->errors()->add(
            'is_published',
            __('products.branch_publication.package_component_pending_review', ['title' => $pendingComponent->title])
        );

        return true;
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
                __('products.branch_publication.package_capacity_exceeded', [
                    'max' => $maxPacks,
                    'committed' => $committed,
                ])
            );
        }
    }
}
