<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AuditLogResource;
use App\Services\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @OA\Tag(
 *     name="AuditLogs",
 *     description="API Endpoints of Audit Logs"
 * )
 */
class AuditLogController extends Controller
{
    private AuditLogService $auditLogService;

    public function __construct(AuditLogService $auditLogService)
    {
        $this->auditLogService = $auditLogService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/audit-logs",
     *     operationId="getAuditLogs",
     *     tags={"AuditLogs"},
     *     summary="Get all audit logs",
     *     description="Returns a list of audit logs",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/AuditLogResource"))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=429, description="Too Many Requests", @OA\JsonContent(example={"status":false,"message":"Too many requests. Please try again later.","code":429}), @OA\Header(header="Retry-After", description="Segundos hasta que se puede volver a intentar", @OA\Schema(type="integer")), @OA\Header(header="X-RateLimit-Limit", description="Límite de peticiones por ventana", @OA\Schema(type="integer")), @OA\Header(header="X-RateLimit-Remaining", description="Peticiones restantes en la ventana actual", @OA\Schema(type="integer")), @OA\Header(header="X-RateLimit-Reset", description="Timestamp de reseteo de ventana", @OA\Schema(type="integer"))),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $logs = $this->auditLogService->getAll();

            return response()->json(AuditLogResource::collection($logs), Response::HTTP_OK);
        } catch (Throwable $e) {
            Log::error('Error in AuditLogController@index', ['error' => $e->getMessage()]);

            return response()->json(['message' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/audit-logs/{id}",
     *     operationId="getAuditLogById",
     *     tags={"AuditLogs"},
     *     summary="Get audit log by ID",
     *     description="Returns a single audit log entry",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/AuditLogResource")),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Not Found"),
     *     @OA\Response(response=429, description="Too Many Requests", @OA\JsonContent(example={"status":false,"message":"Too many requests. Please try again later.","code":429}), @OA\Header(header="Retry-After", description="Segundos hasta que se puede volver a intentar", @OA\Schema(type="integer")), @OA\Header(header="X-RateLimit-Limit", description="Límite de peticiones por ventana", @OA\Schema(type="integer")), @OA\Header(header="X-RateLimit-Remaining", description="Peticiones restantes en la ventana actual", @OA\Schema(type="integer")), @OA\Header(header="X-RateLimit-Reset", description="Timestamp de reseteo de ventana", @OA\Schema(type="integer")))
     * )
     */
    public function show(int $id): JsonResponse
    {
        try {
            $log = $this->auditLogService->getById($id);

            return response()->json(new AuditLogResource($log), Response::HTTP_OK);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Audit log not found'], Response::HTTP_NOT_FOUND);
        } catch (Throwable $e) {
            Log::error('Error in AuditLogController@show', ['id' => $id, 'error' => $e->getMessage()]);

            return response()->json(['message' => 'Internal server error'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
