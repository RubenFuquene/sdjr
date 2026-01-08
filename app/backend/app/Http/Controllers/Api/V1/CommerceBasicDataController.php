<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CommerceBasicDataRequest;
use App\Http\Resources\Api\V1\CommerceBasicDataResource;
use App\Services\CommerceBasicDataService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Nette\Utils\Json;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @OA\Tag(
 *     name="CommercesBasic",
 *     description="Endpoints to create commerce with nested basic data"
 * )
 */
class CommerceBasicDataController extends Controller
{
    use ApiResponseTrait;

    private CommerceBasicDataService $commerceBasicDataService;

    public function __construct(CommerceBasicDataService $service)
    {
        $this->commerceBasicDataService = $service;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/commerces/basic",
     *     tags={"CommercesBasic"},
     *     summary="Create commerce with legal representatives and documents",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *
     *         @OA\JsonContent(ref="#/components/schemas/CommerceBasicDataRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Created", @OA\JsonContent(ref="#/components/schemas/CommerceBasicDataResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Validation Error")
     * )
     */
    public function store(CommerceBasicDataRequest $request): JsonResponse
    {
        try {
            $payload = $request->validated();
            $commerce = $this->commerceBasicDataService->store($payload);            

            return $this->successResponse(new CommerceBasicDataResource($commerce), 'Commerce basic data created successfully', Response::HTTP_CREATED);
        } catch (Throwable $e) {
            Log::error('Error creating commerce basic data', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error creating commerce basic data', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
