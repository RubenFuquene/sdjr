<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CategoryRequest;
use App\Http\Requests\Api\V1\DeleteCategoryRequest;
use App\Http\Requests\Api\V1\IndexCategoryRequest;
use App\Http\Requests\Api\V1\ShowCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Services\CategoryService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Attributes as OA;

/**
 * @OA\Tag(
 *     name="Categories",
 *     description="API Endpoints of Categories"
 * )
 */
class CategoryController extends Controller
{
    use ApiResponseTrait;

    protected CategoryService $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/categories",
     *     operationId="indexCategories",
     *     tags={"Categories"},
     *     summary="List categories",
     *     description="Get paginated list of categories. Permite filtrar por nombre (name), estado (status) y cantidad por página (per_page).",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="name", in="query", required=false, description="Filtrar por nombre de la categoría (texto parcial)", @OA\Schema(type="string")),
     *     @OA\Parameter(name="status", in="query", required=false, description="Filtrar por estado: 1=activos, 0=inactivos", @OA\Schema(type="string", enum={"1","0"}, default="1")),
     *     @OA\Parameter(name="per_page", in="query", required=false, description="Items per page (1-100)", @OA\Schema(type="integer", example=15)),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden")
     * )
     */
    public function index(IndexCategoryRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $filters = $request->validatedFilters();
            $perPage = $request->validatedPerPage();
            $categories = $this->categoryService->getPaginated($filters, $perPage);
            $resource = CategoryResource::collection($categories);

            return $this->paginatedResponse($categories, $resource, 'Categories retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving categories', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created category.
     *
     * @OA\Post(
     *      path="/api/v1/categories",
     *      operationId="storeCategory",
     *      tags={"Categories"},
     *      summary="Store new category",
     *      description="Returns created category data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(type="object")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CategoryResource")
     *      ),
     *
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(CategoryRequest $request): CategoryResource|JsonResponse
    {
        try {
            $category = $this->categoryService->create($request->validated());

            return $this->successResponse(new CategoryResource($category), 'Category created successfully', 201);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error creating category', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified category.
     *
     * @OA\Get(
     *      path="/api/v1/categories/{id}",
     *      operationId="showCategory",
     *      tags={"Categories"},
     *      summary="Get category information",
     *      description="Returns category data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Category ID",
     *
     *          @OA\Schema(ref="#/components/schemas/ShowCategoryRequest")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CategoryResource")
     *      ),
     *
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show(ShowCategoryRequest $request, string $id): CategoryResource|JsonResponse
    {
        try {
            $category = $this->categoryService->find($id);
            if (! $category) {
                return $this->errorResponse('Category not found', 404);
            }

            return $this->successResponse(new CategoryResource($category), 'Category retrieved successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving category', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Update the specified category.
     *
     * @OA\Put(
     *      path="/api/v1/categories/{id}",
     *      operationId="updateCategory",
     *      tags={"Categories"},
     *      summary="Update existing category",
     *      description="Returns updated category data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Category ID",
     *
     *          @OA\Schema(type="string")
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(type="object")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CategoryResource")
     *      ),
     *
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=400, description="Bad Request"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=422, description="Unprocessable Entity"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function update(CategoryRequest $request, string $id): CategoryResource|JsonResponse
    {
        try {
            $category = $this->categoryService->find($id);
            if (! $category) {
                return $this->errorResponse('Category not found', 404);
            }
            $updated = $this->categoryService->update($category, $request->validated());

            return $this->successResponse(new CategoryResource($updated), 'Category updated successfully', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Error updating category', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified category.
     *
     * @OA\Delete(
     *      path="/api/v1/categories/{id}",
     *      operationId="deleteCategory",
     *      tags={"Categories"},
     *      summary="Delete existing category",
     *      description="Deletes a record and returns no content",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="Category ID",
     *
     *          @OA\Schema(ref="#/components/schemas/DeleteCategoryRequest")
     *      ),
     *
     *      @OA\Response(response=204, description="No Content"),
     *      @OA\Response(response=404, description="Resource Not Found"),
     *      @OA\Response(response=401, description="Unauthenticated"),
     *      @OA\Response(response=403, description="Forbidden"),
     *      @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function destroy(DeleteCategoryRequest $request, string $id): JsonResponse
    {
        try {
            $category = $this->categoryService->find($id);
            if (! $category) {
                return $this->errorResponse('Category not found', 404);
            }
            $this->categoryService->delete($category);

            return $this->noContentResponse();
        } catch (\Throwable $e) {
            return $this->errorResponse('Error deleting category', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }
}

