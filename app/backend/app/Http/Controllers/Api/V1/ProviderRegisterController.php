<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ProviderRegisterRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\UserService;
use App\Services\AuthService;
use App\Services\RoleService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @OA\Post(
 *     path="/api/v1/provider/register",
 *     tags={"Providers"},
 *     summary="Register a new provider user",
 *     description="Registers a new provider and returns the session token.",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name","last_name","email","password","password_confirmation"},
 *             @OA\Property(property="name", type="string", example="Proveedor"),
 * *           @OA\Property(property="last_name", type="string", example="Juan"),
 *             @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
 *             @OA\Property(property="password", type="string", format="password", example="secret1234"),
 *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret1234")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Successful registration and login", @OA\JsonContent()),
 *     @OA\Response(response=422, description="Validation Error"),
 *     @OA\Response(response=500, description="Server Error")
 * )
 */
class ProviderRegisterController extends Controller
{
    use ApiResponseTrait;

    private UserService $userService;
    private AuthService $authService;
    private RoleService $roleService;

    public function __construct(UserService $userService, AuthService $authService, RoleService $roleService)
    {
        $this->userService = $userService;
        $this->authService = $authService;
        $this->roleService = $roleService;
    }

    /**
     * Register a new provider and return login response.
     *
     * @param ProviderRegisterRequest $request
     * @return JsonResponse
     */
    public function __invoke(ProviderRegisterRequest $request): JsonResponse
    {
        try {
            // Crear el usuario proveedor
            $user = $this->userService->create($request->validated());
            
            // Asignar rol provider
            $this->roleService->assignToUser($user, ['provider']);

            // Iniciar sesión automáticamente
            $tokenData = $this->authService->login([
                'email' => $user->email,
                'password' => $request->input('password'),
            ]);

            return $this->loginResponse(new UserResource($user), $tokenData['token']);
        } catch (Throwable $e) {
            Log::error('Error registering provider', ['error' => $e->getMessage()]);
            return $this->errorResponse('Error registering provider', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
