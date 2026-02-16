<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DestroyDocumentUploadRequest;
use App\Http\Requests\Api\V1\PatchProductPhotoUploadRequest;
use App\Http\Requests\Api\V1\ProductIndexRequest;
use App\Http\Requests\Api\V1\ShowProductRequest;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Resources\Api\V1\DocumentUploadResource;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\ProductPhoto;
use App\Services\DocumentUploadService;
use App\Services\ProductService;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends Controller
{
    use ApiResponseTrait;

    private ProductService $productService;

    private DocumentUploadService $documentUploadService;

    public function __construct(ProductService $service)
    {
        $this->productService = $service;
        $this->documentUploadService = new DocumentUploadService;
    }

    /**
     * @OA\Get(
     *   path="/api/v1/products",
     *   operationId="indexProduct",
     *   tags={"Products"},
     *   summary="List products",
     *   description="Returns paginated list of products",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="status", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="title", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="description", in="query", required=false, @OA\Schema(type="string")),
     *   @OA\Parameter(name="commerce_id", in="query", required=false, @OA\Schema(type="integer")),
     *   @OA\Parameter(name="product_category_id", in="query", required=false, @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(ProductIndexRequest $request): JsonResponse
    {
        try {
            $products = $this->productService->index($request->validated(), $request->validatedPerPage());

            return $this->successResponse(ProductResource::collection($products));
        } catch (Exception $e) {
            return $this->errorResponse('Error fetching products', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *   path="/api/v1/products",
     *   operationId="storeProduct",
     *   tags={"Products"},
     *   summary="Create product",
     *   description="Creates a new product",
     *   security={{"sanctum":{}}},
     *
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreProductRequest")),
     *
     *   @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/ProductResource")),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->productService->store($request->validated());

            return $this->successResponse(new ProductResource($product), 'Product created successfully', Response::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->errorResponse('Error creating product', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *   path="/api/v1/products/{id}",
     *   operationId="showProduct",
     *   tags={"Products"},
     *   summary="Show product",
     *   description="Returns a single product",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/ProductResource")),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found")
     * )
     */
    public function show(ShowProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productService->show($id);

            return $this->successResponse(new ProductResource($product));
        } catch (Exception $e) {
            return $this->errorResponse('Product not found', Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @OA\Put(
     *   path="/api/v1/products/{id}",
     *   operationId="updateProduct",
     *   tags={"Products"},
     *   summary="Update product",
     *   description="Updates an existing product",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateProductRequest")),
     *
     *   @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/ProductResource")),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            $product = $this->productService->update($id, $request->validated());

            return $this->successResponse(new ProductResource($product), 'Product updated successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            return $this->errorResponse('Error updating product', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *   path="/api/v1/products/{id}",
     *   operationId="destroyProduct",
     *   tags={"Products"},
     *   summary="Delete product",
     *   description="Deletes a product",
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
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->productService->destroy($id);

            return response()->json([], Response::HTTP_NO_CONTENT);
        } catch (Exception $e) {
            return $this->errorResponse('Error deleting product', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get products by commerce ID.
     *
     * @OA\Get(
     *   path="/api/v1/products/commerces/{commerce_id}",
     *   operationId="byCommerceProduct",
     *   tags={"Products"},
     *   summary="List products by commerce",
     *   description="Returns list of products for a specific commerce",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="commerce_id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function byCommerce(int $commerce_id): JsonResponse
    {
        try {
            $products = $this->productService->getByCommerce($commerce_id);

            return $this->successResponse(ProductResource::collection($products), 'Products fetched successfully', Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorResponse('Error fetching products by commerce', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get products by commerce branch ID.
     *
     * @OA\Get(
     *   path="/api/v1/products/branches/{branch_id}",
     *   operationId="byCommerceBranchProduct",
     *   tags={"Products"},
     *   summary="List products by commerce branch",
     *   description="Returns list of products for a specific commerce branch",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="branch_id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function byCommerceBranch(int $branch_id): JsonResponse
    {
        try {
            $products = $this->productService->getByCommerceBranch($branch_id);

            return $this->successResponse(ProductResource::collection($products), 'Products fetched successfully', Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_NOT_FOUND);
        } catch (Exception $e) {
            return $this->errorResponse('Error fetching products by commerce branch', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store product package items.
     *
     * @OA\Post(
     *   path="/api/v1/products/package-items",
     *   operationId="storeProductPackageItems",
     *   tags={"Products"},
     *   summary="Store product package items",
     *   description="Stores items for a product package",
     *   security={{"sanctum":{}}},
     *
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreProductRequest")),
     *
     *   @OA\Response(response=200, description="Successful operation"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function storePackageItems(StoreProductRequest $request): JsonResponse
    {
        try {
            $productPackage = $this->productService->storePackageItems($request->validated());

            return $this->successResponse(new ProductResource($productPackage), 'Product package items stored successfully', Response::HTTP_OK);

        } catch (Exception $e) {
            Log::error('Error storing product package items', ['error' => $e->getMessage().' on line '.$e->getLine()]);

            return $this->errorResponse('Error storing product package items', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update product package items.
     *
     * @OA\Put(
     *   path="/api/v1/products/package-items/{product_package_id}",
     *   operationId="updateProductPackageItems",
     *   tags={"Products"},
     *   summary="Update product package items",
     *   description="Updates items for a product package",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="product_package_id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateProductRequest")),
     *
     *   @OA\Response(response=200, description="Successful operation"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=404, description="Not found"),
     *   @OA\Response(response=422, description="Validation error")
     * )
     */
    public function updatePackageItems(UpdateProductRequest $request, int $product_package_id): JsonResponse
    {
        try {
            $product = $this->productService->updatePackageItems($product_package_id, $request->validated());

            return $this->successResponse(new ProductResource($product), 'Product package items updated successfully', Response::HTTP_OK);
        } catch (Exception $e) {
            Log::error('Error updating product package items', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error updating product package items', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Confirm product photo upload.
     *
     * @OA\Patch(
     *   path="/api/v1/products/photos/confirm",
     *   operationId="confirmProductPhotoUpload",
     *   tags={"Products"},
     *   summary="Confirm product photo upload",
     *   description="Confirma que la foto del producto fue subida exitosamente y actualiza el registro.",
     *   security={{"sanctum":{}}},
     *
     *   @OA\RequestBody(
     *     required=true,
     *
     *     @OA\JsonContent(ref="#/components/schemas/PatchProductPhotoUploadRequest")
     *   ),
     *
     *   @OA\Response(
     *     response=200,
     *     description="Product photo confirmed successfully",
     *
     *     @OA\JsonContent(ref="#/components/schemas/DocumentUploadResource")
     *   ),
     *
     *   @OA\Response(response=404, description="Photo not exist or not in pending status"),
     *   @OA\Response(response=410, description="Presigned URL expired"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden"),
     *   @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function confirmPhotoUpload(PatchProductPhotoUploadRequest $request): JsonResponse
    {
        try {

            $data = $request->validated();

            $product_photo = $this->documentUploadService->getDocumentPendingByToken(ProductPhoto::class, $data['upload_token']);

            // Validations
            if ($product_photo->expires_at < now()) {
                return $this->errorResponse('The presigned URL has expired.', 410);
            }

            // Update photo status to uploaded
            $this->documentUploadService->confirmUpload($product_photo, $data);

            return $this->successResponse(new DocumentUploadResource($product_photo, ['product_id' => $product_photo->id]), 'Product photo confirmed successfully', 200);

        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Photo not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage().' on line '.$e->getLine(), $e->getCode() ?: 500);
        }
    }

    /**
     * Remove a product photo.
     *
     * @OA\Delete(
     *   path="/api/v1/products/photos/{photo}",
     *   operationId="removeProductPhoto",
     *   tags={"Products"},
     *   summary="Remove product photo",
     *   description="Elimina una foto de producto por su ID.",
     *   security={{"sanctum":{}}},
     *
     *   @OA\Parameter(name="photo", in="path", required=true, @OA\Schema(type="integer")),
     *
     *   @OA\Response(response=204, description="Photo deleted successfully"),
     *   @OA\Response(response=404, description="Photo not found"),
     *   @OA\Response(response=401, description="Unauthenticated"),
     *   @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function removePhoto(DestroyDocumentUploadRequest $request, int $photo_id): JsonResponse
    {
        try {

            $this->documentUploadService->removeDocument(ProductPhoto::class, $photo_id);

            return $this->successResponse([], 'Photo deleted successfully', 204);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return $this->errorResponse('Photo not found', 404);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage().' on line '.$e->getLine(), $e->getCode() ?: 500);
        }
    }
}
