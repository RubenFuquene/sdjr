<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\DeleteOrderRequest;
use App\Http\Requests\Api\V1\IndexOrderRequest;
use App\Http\Requests\Api\V1\PatchOrderStatusRequest;
use App\Http\Requests\Api\V1\ShowOrderRequest;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Requests\Api\V1\UpdateOrderRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Services\OrderService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @OA\Tag(
 *     name="Orders",
 *     description="API Endpoints of Orders"
 * )
 */
class OrderController extends Controller
{
    use ApiResponseTrait;

    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders",
     *     operationId="indexOrders",
     *     tags={"Orders"},
     *     summary="List orders",
     *     description="Returns a list of orders filtered by optional query params.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="status", in="query", required=false, description="Filter by order status", @OA\Schema(type="string", example="pending")),
     *     @OA\Parameter(name="commerce_branch_id", in="query", required=false, description="Filter by commerce branch ID", @OA\Schema(type="integer", example=1)),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", @OA\Property(property="status", type="boolean", example=true), @OA\Property(property="message", type="string", example="Orders retrieved successfully"), @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OrderResource")))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function index(IndexOrderRequest $request): JsonResponse
    {
        try {
            $orders = $this->orderService->index($request->validated());

            return $this->successResponse(OrderResource::collection($orders), 'Orders retrieved successfully', Response::HTTP_OK);
        } catch (Throwable $e) {
            Log::error('Error listing orders', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error listing orders', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders",
     *     operationId="storeOrder",
     *     tags={"Orders"},
     *     summary="Create a new order",
     *     description="Creates a new order for the authenticated user.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/StoreOrderRequest")),
     *
     *     @OA\Response(response=201, description="Order created successfully", @OA\JsonContent(type="object", @OA\Property(property="status", type="boolean", example=true), @OA\Property(property="message", type="string", example="Order created successfully"), @OA\Property(property="data", ref="#/components/schemas/OrderResource"))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=422, description="Unprocessable Entity"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $order = $this->orderService->store($data);

            return $this->createdResponse(new OrderResource($order), 'Order created successfully');
        } catch (Throwable $e) {
            Log::error('Order creation failed', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error creating order', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/{id}",
     *     operationId="showOrder",
     *     tags={"Orders"},
     *     summary="Get order by ID",
     *     description="Returns a single order with its items.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Order ID", @OA\Schema(ref="#/components/schemas/ShowOrderRequest")),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", @OA\Property(property="status", type="boolean", example=true), @OA\Property(property="message", type="string", example="Order retrieved successfully"), @OA\Property(property="data", ref="#/components/schemas/OrderResource"))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function show(ShowOrderRequest $request, int $id): JsonResponse
    {
        try {
            $order = $this->orderService->show($id);
            if (! $order) {
                return $this->errorResponse('Order not found', Response::HTTP_NOT_FOUND);
            }

            return $this->successResponse(new OrderResource($order), 'Order retrieved successfully', Response::HTTP_OK);
        } catch (Throwable $e) {
            Log::error('Error retrieving order', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error retrieving order', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Put(
     *     path="/api/v1/orders/{id}",
     *     operationId="updateOrder",
     *     tags={"Orders"},
     *     summary="Update order",
     *     description="Updates an order state.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Order ID", @OA\Schema(type="integer", example=123)),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/UpdateOrderRequest")),
     *
     *     @OA\Response(response=200, description="Order updated successfully", @OA\JsonContent(type="object", @OA\Property(property="status", type="boolean", example=true), @OA\Property(property="message", type="string", example="Order updated successfully"), @OA\Property(property="data", ref="#/components/schemas/OrderResource"))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=422, description="Unprocessable Entity"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function update(UpdateOrderRequest $request, int $id): JsonResponse
    {
        try {
            $order = $this->orderService->update($id, $request->validated());
            if (! $order) {
                return $this->errorResponse('Order not found', Response::HTTP_NOT_FOUND);
            }

            return $this->successResponse(new OrderResource($order), 'Order updated successfully', Response::HTTP_OK);
        } catch (Throwable $e) {
            Log::error('Order update failed', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error updating order', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Patch(
     *     path="/api/v1/orders/{id}/status",
     *     operationId="patchOrderStatus",
     *     tags={"Orders"},
     *     summary="Patch order status",
     *     description="Updates only the order status.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Order ID", @OA\Schema(type="integer", example=123)),
     *
     *     @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/PatchOrderStatusRequest")),
     *
     *     @OA\Response(response=200, description="Order status updated successfully", @OA\JsonContent(type="object", @OA\Property(property="status", type="boolean", example=true), @OA\Property(property="message", type="string", example="Order status updated successfully"), @OA\Property(property="data", ref="#/components/schemas/OrderResource"))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=422, description="Unprocessable Entity"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function patchStatus(PatchOrderStatusRequest $request, int $id): JsonResponse
    {
        try {
            $order = $this->orderService->patchStatus($id, (string) $request->validated('status'));
            if (! $order) {
                return $this->errorResponse('Order not found', Response::HTTP_NOT_FOUND);
            }

            return $this->successResponse(new OrderResource($order), 'Order status updated successfully', Response::HTTP_OK);
        } catch (\DomainException $e) {
            return $this->errorResponse('Invalid order status transition', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            Log::error('Order status patch failed', ['error' => $e->getMessage(), 'order_id' => $id]);

            return $this->errorResponse('Error updating order status', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/{id}",
     *     operationId="deleteOrder",
     *     tags={"Orders"},
     *     summary="Delete order",
     *     description="Deletes an order and returns no content.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="id", in="path", required=true, description="Order ID", @OA\Schema(ref="#/components/schemas/DeleteOrderRequest")),
     *
     *     @OA\Response(response=204, description="No Content"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=404, description="Order not found"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function destroy(DeleteOrderRequest $request, int $id): JsonResponse
    {
        try {
            $result = $this->orderService->destroy($id);
            if (! $result) {
                return $this->errorResponse('Order not found or not deleted', Response::HTTP_NOT_FOUND);
            }

            return $this->noContentResponse();
        } catch (Throwable $e) {
            Log::error('Order deletion failed', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error deleting order', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/my-orders",
     *     operationId="myOrders",
     *     tags={"Orders"},
     *     summary="List authenticated user orders",
     *     description="Returns the authenticated user's orders.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", @OA\Property(property="status", type="boolean", example=true), @OA\Property(property="message", type="string", example="My orders retrieved successfully"), @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OrderResource")))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function myOrders(Request $request): JsonResponse
    {
        try {
            $orders = $this->orderService->getByUser($request->user()->id);

            return $this->successResponse(OrderResource::collection($orders), 'My orders retrieved successfully', Response::HTTP_OK);
        } catch (Throwable $e) {
            Log::error('Error listing authenticated user orders', ['error' => $e->getMessage()]);

            return $this->errorResponse('Error listing my orders', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/commerce-branches/{branchId}/orders",
     *     operationId="commerceBranchOrders",
     *     tags={"Orders"},
     *     summary="List orders by commerce branch",
     *     description="Returns all orders of a specific commerce branch.",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(name="branchId", in="path", required=true, description="Commerce branch ID", @OA\Schema(type="integer", example=1)),
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(type="object", @OA\Property(property="status", type="boolean", example=true), @OA\Property(property="message", type="string", example="Commerce branch orders retrieved successfully"), @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/OrderResource")))),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Forbidden"),
     *     @OA\Response(response=500, description="Internal Server Error")
     * )
     */
    public function commerceBranchOrders(IndexOrderRequest $request, int $branchId): JsonResponse
    {
        try {
            $orders = $this->orderService->getByCommerceBranch($branchId);

            return $this->successResponse(OrderResource::collection($orders), 'Commerce branch orders retrieved successfully', Response::HTTP_OK);
        } catch (Throwable $e) {
            Log::error('Error listing commerce branch orders', ['error' => $e->getMessage(), 'branch_id' => $branchId]);

            return $this->errorResponse('Error listing commerce branch orders', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
