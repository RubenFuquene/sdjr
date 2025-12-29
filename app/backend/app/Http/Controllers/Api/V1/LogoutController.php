<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    use ApiResponseTrait;

    /**
     * @OA\Post(
     *     path="/api/v1/logout",
     *     operationId="logoutUser",
     *     tags={"Auth"},
     *     summary="Logout current authenticated user",
     *     description="Revokes the current user's token and logs out.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if ($user) {
                $user->currentAccessToken()?->delete();
            }
            return $this->successResponse(null, 'Successfully logged out', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Logout failed', 500, app()->environment('production') ? null : ['exception' => $e->getMessage()]);
        }
    }
}
