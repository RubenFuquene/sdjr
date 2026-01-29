<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DestroyProductCategoryRequest;
use App\Http\Requests\Api\V1\ProductCategoryIndexRequest;
use App\Http\Requests\Api\V1\ShowProductCategoryRequest;
use App\Http\Requests\Api\V1\StoreProductCategoryRequest;
use App\Http\Requests\Api\V1\UpdateProductCategoryRequest;
use App\Http\Resources\Api\V1\ProductCategoryResource;
use App\Services\ProductCategoryService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProductCategoryController extends Controller
{
    use ApiResponseTrait;

    private ProductCategoryService $service;

    public function __construct(ProductCategoryService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *   path="/api/v1/product-categories",
     *   operationId="indexProductCategory",
     *   tags={"ProductCategories"},
     *   summary="List product categories",
     *   description="Returns paginated list of product categories",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="name", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="description", in="query", required=false, @OA\Schema(type="string")),
     *
     *   @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(ProductCategoryIndexRequest $request): JsonResponse
    {
        try {
            $categories = $this->service->index($request->validated(), $request->validatedPerPage());

            return $this->successResponse(ProductCategoryResource::collection($categories));
        } catch (Exception $e) {
            return $this->errorResponse('Error fetching product categories', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/v1/product-categories",
     *   operationId="storeProductCategory",
     *   tags={"ProductCategories"},
     *   summary="Create product category",
     *   description="Creates a new product category",
     *   security={{"sanctum":{}}},
     *
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreProductCategoryRequest")),
     *
     *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ProductCategoryResource")),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        try {
            $category = $this->service->store($request->validated());

            return $this->successResponse(new ProductCategoryResource($category), 'Product Category created successfully', Response::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->errorResponse('Error creating product category', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/v1/product-categories/{id}",
     *   operationId="showProductCategory",
     *   tags={"ProductCategories"},
     *   summary="Show product category",
     *   description="Returns a single product category",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/ProductCategoryResource")),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(ShowProductCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $category = $this->service->show($id);

            return $this->successResponse(new ProductCategoryResource($category));
        } catch (Exception $e) {
            return $this->errorResponse('Product category not found', Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Put(
     *   path="/api/v1/product-categories/{id}",
     *   operationId="updateProductCategory",
     *   tags={"ProductCategories"},
     *   summary="Update product category",
     *   description="Updates an existing product category",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateProductCategoryRequest")),
     *
     *   @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/ProductCategoryResource")),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateProductCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $category = $this->service->update($id, $request->validated());

            return $this->successResponse(new ProductCategoryResource($category), 'Product Category updated successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse('Error updating product category', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/v1/product-categories/{id}",
     *   operationId="destroyProductCategory",
     *   tags={"ProductCategories"},
     *   summary="Delete product category",
     *   description="Deletes a product category",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=204, description="No Content"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function destroy(DestroyProductCategoryRequest $request, int $id): JsonResponse
    {
        try {
            $this->service->destroy($id);

            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return $this->errorResponse('Error deleting product category', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
