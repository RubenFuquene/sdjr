<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\NearbyBranchesRequest;
use App\Http\Requests\Api\V1\NearbyProductsRequest;
use App\Http\Resources\Api\V1\NearbyBranchResource;
use App\Http\Resources\Api\V1\NearbyProductResource;
use App\Services\NearbySearchService;
use App\Traits\ApiResponseTrait;

class NearbyController extends Controller
{
    use ApiResponseTrait;

    public function __construct(private readonly NearbySearchService $nearbySearchService) {}

    /** @OA.Get(
     * path="/api/v1/nearby/branches",
     * summary="Get nearby branches ordered by distance",
     * tags={"Nearby"},
     *
     * @OA.Parameter(name="latitude", in="query", required=true, description="Latitude of the current location", @OA.Schema(type="number", format="float", example=19.4326)),
     *
     * @OA.Parameter(name="longitude", in="query", required=true, description="Longitude of the current location", @OA.Schema(type="number", format="float", example=-99.1332)),
     *
     * @OA.Parameter(name="radius", in="query", required=false, description="Search radius in kilometers", @OA.Schema(type="integer", example=10)),
     *
     * @OA.Parameter(name="per_page", in="query", required=false, description="Results per page", @OA.Schema(type="integer", example=15)),
     *
     * @OA.Response(response=200, description="Paginated list of nearby branches", @OA.JsonContent(@OA.Property(property="data", type="array", @OA.Items(ref="#/components/schemas/NearbyBranchResource")), @OA.Property(property="links", type="object"), @OA.Property(property="meta", type="object", @OA.Property(property="current_page", type="integer"), @OA.Property(property="last_page", type="integer"), @OA.Property(property="per_page", type="integer"), @OA.Property(property="total", type="integer")))),
     *
     * @OA.Response(response=422, description="Validation error"),
     *
     * @OA.Response(response=500, description="Internal server error"),
     *
     * @OA.Response(
     *     response=429,
     *     description="Too Many Requests",
     *
     *     @OA.JsonContent(example={"status":false,"message":"Too many requests. Please try again later.","code":429}),
     *
     *     @OA.Header(header="Retry-After", description="Seconds to retry", @OA.Schema(type="integer")),
     *
     *     @OA.Header(header="X-RateLimit-Limit", description="Rate limit per window", @OA.Schema(type="integer")),
     *
     *     @OA.Header(header="X-RateLimit-Remaining", description="Requests left in window", @OA.Schema(type="integer")),
     *
     *     @OA.Header(header="X-RateLimit-Reset", description="Window reset timestamp", @OA.Schema(type="integer"))
     * )
     */
    public function branches(NearbyBranchesRequest $request)
    {
        try {
            $branches = $this->nearbySearchService->nearbyBranches(
                (float) $request->input('latitude'),
                (float) $request->input('longitude'),
                $request->validatedRadius(),
                $request->validatedPerPage()
            );
            $resources = NearbyBranchResource::collection($branches);

            return $this->paginatedResponse($branches, $resources, 'Nearby branches retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch nearby branches', 500, [$e->getMessage()]);
        }
    }

    /** @OA.Get(
     * path="/api/v1/nearby/products",
     * summary="Get products available at nearby branches",
     * tags={"Nearby"},
     *
     * @OA.Parameter(name="latitude", in="query", required=true, description="Latitude of the current location", @OA.Schema(type="number", format="float", example=19.4326)),
     *
     * @OA.Parameter(name="longitude", in="query", required=true, description="Longitude of the current location", @OA.Schema(type="number", format="float", example=-99.1332)),
     *
     * @OA.Parameter(name="radius", in="query", required=false, description="Search radius in kilometers", @OA.Schema(type="integer", example=10)),
     *
     * @OA.Parameter(name="category_id", in="query", required=false, description="Product category ID", @OA.Schema(type="integer", example=5)),
     *
     * @OA.Parameter(name="max_price", in="query", required=false, description="Maximum product price", @OA.Schema(type="number", format="float", example=100.00)),
     *
     * @OA.Parameter(name="per_page", in="query", required=false, description="Results per page", @OA.Schema(type="integer", example=15)),
     *
     * @OA.Response(response=200, description="Paginated list of products with nearest branch distance", @OA.JsonContent(@OA.Property(property="data", type="array", @OA.Items(ref="#/components/schemas/NearbyProductResource")), @OA.Property(property="links", type="object"), @OA.Property(property="meta", type="object", @OA.Property(property="current_page", type="integer"), @OA.Property(property="last_page", type="integer"), @OA.Property(property="per_page", type="integer"), @OA.Property(property="total", type="integer")))),
     *
     * @OA.Response(response=422, description="Validation error"),
     *
     * @OA.Response(response=500, description="Internal server error"),
     *
     * @OA.Response(
     *     response=429,
     *     description="Too Many Requests",
     *
     *     @OA.JsonContent(example={"status":false,"message":"Too many requests. Please try again later.","code":429}),
     *
     *     @OA.Header(header="Retry-After", description="Seconds to retry", @OA.Schema(type="integer")),
     *
     *     @OA.Header(header="X-RateLimit-Limit", description="Rate limit per window", @OA.Schema(type="integer")),
     *
     *     @OA.Header(header="X-RateLimit-Remaining", description="Requests left in window", @OA.Schema(type="integer")),
     *
     *     @OA.Header(header="X-RateLimit-Reset", description="Window reset timestamp", @OA.Schema(type="integer"))
     * )
     */
    public function products(NearbyProductsRequest $request)
    {
        try {
            $products = $this->nearbySearchService->nearbyProducts(
                (float) $request->input('latitude'),
                (float) $request->input('longitude'),
                $request->validatedRadius(),
                $request->filters(),
                $request->validatedPerPage()
            );
            $resources = NearbyProductResource::collection($products);

            return $this->paginatedResponse($products, $resources, 'Nearby products retrieved successfully');
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch nearby products', 500, [$e->getMessage()]);
        }
    }
}
