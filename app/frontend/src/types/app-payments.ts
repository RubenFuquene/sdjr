/**
 * Tipos del módulo app para el pago de órdenes.
 * Alineados al contrato del backend: TransactionResource + POST /orders/{id}/transactions.
 */

export type TransactionStatus = "initiated" | "approved" | "rejected" | "failed";

/** Transacción devuelta por el backend (TransactionResource). El payload crudo del gateway nunca viaja. */
export interface AppTransaction {
  id: number;
  order_id: number;
  payment_method_id: number | null;
  provider: string;
  external_id: string | null;
  status: TransactionStatus;
  amount: number;
  currency: string;
  failure_reason: string | null;
  created_at?: string;
}

/**
 * Cuerpo de POST /orders/{id}/transactions.
 * `simulate: "reject"` solo lo respeta la pasarela fake (camino de prueba QA);
 * una pasarela real lo ignora.
 */
export interface PayOrderPayload {
  payment_method_id?: number;
  simulate?: "reject";
}

/**
 * Resultado tipado del pago. El backend responde 201 (aprobado) o 402
 * (rechazado, con la transacción en el cuerpo); ambos son resultados de
 * negocio válidos para la UI — no excepciones.
 */
export type PayOrderResult =
  | { approved: true; transaction: AppTransaction }
  | { approved: false; transaction: AppTransaction | null; reason: string };
