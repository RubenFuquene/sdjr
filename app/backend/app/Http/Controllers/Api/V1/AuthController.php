<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\AuthService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints for User Authentication"
 * )
 */
class AuthController extends Controller
{
    use ApiResponseTrait;

    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/login",
     *     summary="Authenticate user and get token",
     *     tags={"Authentication"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(
     *             type="object",
     *             example={"email": "user@example.com", "password": "password123"}
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *
     *         @OA\JsonContent(
     *             example={
     *                 "message": "Login successful",
     *                 "data": {
     *                     "id": 1,
     *                     "name": "John",
     *                     "last_name": "Doe",
     *                     "email": "john.doe@example.com",
     *                     "phone": "3001234567",
     *                     "roles": {"admin", "user"},
     *                     "status": "A",
     *                     "created_at": "2023-01-01T12:00:00Z",
     *                     "updated_at": "2023-01-01T12:00:00Z"
     *                 },
     *                 "token": "1|laravel_sanctum_token_string"
     *             }
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation error or Invalid credentials",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="message", type="string", example="The given data was invalid."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *
     *     @OA\Response(response=429, description="Too many requests, please try again later.")
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login($request->validated());

        return $this->loginResponse(new UserResource($data['user']), $data['token']);
    }
}
