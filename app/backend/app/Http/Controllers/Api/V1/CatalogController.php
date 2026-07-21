<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CommerceBranchResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Services\CommerceBranchService;
use App\Services\ProductService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Lectura pública de catálogo por id, para consumo de la app cliente.
 *
 * Distinto de los endpoints `products/{id}` y `commerce-branches/{id}`
 * (apiResource protegidos, permisos provider.*): este controlador solo
 * expone productos/sucursales activos, sin autenticación, en el mismo
 * shape que los recursos de `nearby` para que el frontend reutilice
 * un único set de tipos/adaptadores.
 */
class CatalogController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private readonly ProductService $productService,
        private readonly CommerceBranchService $commerceBranchService
    ) {}

    /** @OA.Get(
     * path="/api/v1/catalog/products/{id}",
     * summary="Get a single active product by id (public, customer app)",
     * tags={"Catalog"},
     *
     * @OA.Parameter(name="id", in="path", required=true, @OA.Schema(type="integer", example=10)),
     *
     * @OA.Response(response=200, description="Product detail", @OA.JsonContent(@OA.Property(property="data", ref="#/components/schemas/ProductResource"))),
     *
     * @OA.Response(response=404, description="Product not found or not active"),
     *
     * @OA.Response(response=429, description="Too Many Requests")
     * )
     */
    public function product(int $id): JsonResponse
    {
        try {
            $product = $this->productService->showPublic($id);

            return $this->successResponse(new ProductResource($product), 'Product retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Product not found', Response::HTTP_NOT_FOUND);
        }
    }

    /** @OA.Get(
     * path="/api/v1/catalog/commerce-branches/{id}",
     * summary="Get a single active commerce branch by id (public, customer app)",
     * tags={"Catalog"},
     *
     * @OA.Parameter(name="id", in="path", required=true, @OA.Schema(type="integer", example=1)),
     *
     * @OA.Response(response=200, description="Commerce branch detail", @OA.JsonContent(@OA.Property(property="data", ref="#/components/schemas/CommerceBranchResource"))),
     *
     * @OA.Response(response=404, description="Branch not found or not active"),
     *
     * @OA.Response(response=429, description="Too Many Requests")
     * )
     */
    public function branch(int $id): JsonResponse
    {
        try {
            $branch = $this->commerceBranchService->showPublic($id);

            return $this->successResponse(new CommerceBranchResource($branch), 'Commerce branch retrieved successfully');
        } catch (Exception $e) {
            return $this->errorResponse('Commerce branch not found', Response::HTTP_NOT_FOUND);
        }
    }
}
