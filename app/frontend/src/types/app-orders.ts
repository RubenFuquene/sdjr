/**
 * Tipos del módulo app para creación y lectura de órdenes.
 * Alineados al contrato del backend: StoreOrderRequest + OrderResource.
 */

/** Estados de orden definidos en el backend (Constant::ORDER_STATUS_*). */
export type AppOrderStatus =
  | "pending"
  | "confirmed"
  | "preparing"
  | "ready"
  | "delivered"
  | "cancelled";

/** Ítem que el cliente envía al crear una orden. */
export interface CreateOrderItemInput {
  product_id: number;
  quantity: number;
}

/**
 * Cuerpo de POST /api/v1/orders.
 * `user_id` NO se envía: el backend lo fija desde el token (ver StoreOrderRequest).
 */
export interface CreateOrderPayload {
  commerce_branch_id: number;
  items: CreateOrderItemInput[];
}

/** Ítem de orden devuelto por el backend (OrderItemResource). */
export interface AppOrderItem {
  id: number;
  order_id: number;
  product_id: number;
  quantity: number;
  unit_price: number;
  subtotal: number;
  created_at?: string;
  updated_at?: string;
}

/**
 * Orden devuelta por el backend (OrderResource).
 * `user` y `commerce_branch` llegan como objetos anidados; se tipan laxos
 * porque el flujo de compra solo consume id/estado/total/ítems.
 */
export interface AppOrder {
  id: number;
  total_price: number;
  status: AppOrderStatus;
  items?: AppOrderItem[];
  created_at?: string;
  updated_at?: string;
}

/** Envelope estándar del backend: { status, message, data }. */
export interface CreateOrderResponse {
  status: boolean;
  message: string | null;
  data: AppOrder;
}
