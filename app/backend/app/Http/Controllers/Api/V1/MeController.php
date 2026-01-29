<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MeRequest;
use App\Http\Resources\Api\V1\MePermissionsResource;
use App\Http\Resources\Api\V1\MeResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class MeController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Get(
     *     path="/api/v1/me",
     *     operationId="getMe",
     *     tags={"Auth"},
     *     summary="Get current authenticated user info",
     *     description="Returns id, name, email, roles and permissions of the authenticated user.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="request",
     *         in="query",
     *         required=false,
     *         description="MeRequest query params",
     *
     *         @OA\Schema(ref="#/components/schemas/MeRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function authenticatedUser(MeRequest $request): JsonResponse
    {
        try {
            return $this->successResponse(
                new MeResource($request->user()),
                'User information retrieved successfully',
                200
            );

        } catch (\Exception $e) {
            return $this->errorResponse(['error' => 'Unable to retrieve user information'], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/me/permissions",
     *     operationId="getMePermissions",
     *     tags={"Auth"},
     *     summary="Get current authenticated user permissions and roles",
     *     description="Returns permissions and roles of the authenticated user.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="request",
     *         in="query",
     *         required=false,
     *         description="MeRequest query params",
     *
     *         @OA\Schema(ref="#/components/schemas/MeRequest")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function authenticatedUserPermissions(MeRequest $request): JsonResponse
    {
        try {
            return $this->successResponse(
                new MePermissionsResource($request->user()),
                'Permissions and roles retrieved successfully',
                200
            );
        } catch (\Exception $e) {
            return $this->errorResponse(['error' => 'Unable to retrieve user permissions'], 500);
        }
    }
}
