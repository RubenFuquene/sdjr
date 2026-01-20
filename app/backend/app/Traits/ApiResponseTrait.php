<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Trait to centralize generic API responses for controllers.
 */
trait ApiResponseTrait
{
    /**
     * Return a successful response with data.
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Return a response for resource creation.
     */
    protected function createdResponse(mixed $data = null, ?string $message = null): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    /**
     * Return a response for no content (successful deletion).
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return an error response.
     */
    protected function errorResponse(string $message, int $code = 400, ?array $errors = null): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];
        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    /**
     * Return a validation error response.
     */
    protected function validationErrorResponse(array $errors, ?string $message = null): JsonResponse
    {
        return $this->errorResponse($message ?? 'Validation error', 422, $errors);
    }

    /**
     * Return a paginated response with uniform structure.
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, AnonymousResourceCollection $resourceCollection, ?string $message = null): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $resourceCollection->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ], 200);
    }

    /**
     * Return a login response with token.
     */
    protected function loginResponse(mixed $data = null, $token = '', ?string $message = 'Login successful', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
            'token' => $token
        ], $code);
        
    }
}
