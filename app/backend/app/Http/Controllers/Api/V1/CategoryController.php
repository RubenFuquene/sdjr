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
     * Display a listing of the resource.
     *
     * @OA\Get(
     *      path="/api/v1/categories",
     *      operationId="getCategoriesList",
     *      tags={"Categories"},
     *      summary="Get list of categories",
     *      description="Returns list of categories. Permite filtrar por número de páginas (per_page) y estado (status: 1=activos, 0=inactivos, all=todos).",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="per_page",
     *          in="query",
     *          description="Cantidad de registros por página (1-100)",
     *          required=false,
     *
     *          @OA\Schema(type="integer", default=15)
     *      ),
     *
     *      @OA\Parameter(
     *          name="status",
     *          in="query",
     *          description="Filtrar por estado: 1=activos, 0=inactivos, all=todos",
     *          required=false,
     *
     *          @OA\Schema(type="string", enum={"1","0","all"}, default="all")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CategoryResource")
     *       ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     *     )
     *
     * @return AnonymousResourceCollection
     */
    public function index(IndexCategoryRequest $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $perPage = $request->validatedPerPage();
            $status = $request->validatedStatus();
            $categories = $this->categoryService->getPaginated($perPage, $status);
            $resource = CategoryResource::collection($categories);

            return $this->paginatedResponse($categories, $resource, 'Categories retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Error retrieving categories', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *      path="/api/v1/categories",
     *      operationId="storeCategory",
     *      tags={"Categories"},
     *      summary="Store new category",
     *      description="Returns category data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/CategoryRequest")
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CategoryResource")
     *       ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     *
     * @return CategoryResource
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
     * Display the specified resource.
     *
     * @OA\Get(
     *      path="/api/v1/categories/{id}",
     *      operationId="getCategoryById",
     *      tags={"Categories"},
     *      summary="Get category information",
     *      description="Returns category data",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="id",
     *          description="Category id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CategoryResource")
     *       ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
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
     * Update the specified resource in storage.
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
     *          description="Category id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(ref="#/components/schemas/CategoryRequest")
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CategoryResource")
     *       ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
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
     * Remove the specified resource from storage.
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
     *          description="Category id",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="message", type="string", example="Category deleted successfully")
     *          )
     *       ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource Not Found"
     *      )
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
