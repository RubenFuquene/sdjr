"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { useSearchParams } from "next/navigation";
import { AlertCircle, ArrowLeft, CheckCircle2, CreditCard, FlaskConical, Store } from "lucide-react";
import { ApiError } from "@/lib/api/client";
import { getProductDetail } from "@/lib/api/app-catalog";
import { createOrder, isInsufficientStockError } from "@/lib/api/app-orders";
import { payOrder } from "@/lib/api/app-payments";
import { mapProductDetailToView, type ProductDetailView } from "@/types/app-catalog.adapters";
import type { AppOrder } from "@/types/app-orders";
import type { AppTransaction } from "@/types/app-payments";

/**
 * Selector de pago simulado (no hay pasarela real contratada aún).
 * Opciones honestas: nada de tarjetas ficticias con apariencia real.
 * "reject" expone el camino de rechazo determinista para QA.
 * Cuando llegue la pasarela real, esto se reemplaza por los métodos
 * reales del usuario (payment_methods) — ver SCRUM-352.
 */
const SIMULATED_PAYMENT_OPTIONS = [
  {
    id: "approve",
    label: "Pago simulado",
    detail: "Aprobación inmediata (pasarela de prueba)",
    isDefault: true,
  },
  {
    id: "reject",
    label: "Pago simulado — rechazo",
    detail: "Fuerza un rechazo para pruebas de QA",
    isDefault: false,
  },
] as const;

type SimulatedOptionId = (typeof SIMULATED_PAYMENT_OPTIONS)[number]["id"];

type LoadState = "loading" | "not-found" | "error" | "loaded";
type OrderState = "idle" | "submitting" | "created" | "error";
type PayState = "idle" | "paying" | "approved" | "rejected" | "error";

function formatPrice(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

function parsePositiveInt(value: string | null): number | null {
  if (!value) {
    return null;
  }

  const parsed = Number.parseInt(value, 10);
  return Number.isInteger(parsed) && parsed > 0 ? parsed : null;
}

export default function AppCartPage() {
  const searchParams = useSearchParams();
  const productId = parsePositiveInt(searchParams.get("productId"));
  const branchId = parsePositiveInt(searchParams.get("branchId"));

  const [loadState, setLoadState] = useState<LoadState>("loading");
  const [product, setProduct] = useState<ProductDetailView | null>(null);
  const [quantity, setQuantity] = useState(1);
  const [selectedPaymentId, setSelectedPaymentId] = useState<SimulatedOptionId>("approve");
  const [orderState, setOrderState] = useState<OrderState>("idle");
  const [orderError, setOrderError] = useState<string | null>(null);
  const [createdOrder, setCreatedOrder] = useState<AppOrder | null>(null);
  const [payState, setPayState] = useState<PayState>("idle");
  const [payError, setPayError] = useState<string | null>(null);
  const [transaction, setTransaction] = useState<AppTransaction | null>(null);

  useEffect(() => {
    // Sin productId/branchId no hay nada que buscar; el render maneja esos
    // casos directamente (son conocidos de forma sincrona desde la URL, no
    // requieren estado ni fetch).
    if (!productId || !branchId) {
      return;
    }

    let cancelled = false;

    async function loadProduct() {
      setLoadState("loading");

      try {
        const response = await getProductDetail(productId!);

        if (cancelled) {
          return;
        }

        const view = mapProductDetailToView(response.data);
        setProduct(view);
        setQuantity((prev) => Math.min(Math.max(prev, 1), Math.max(view.quantityAvailable, 1)));
        setLoadState("loaded");
      } catch (error) {
        if (cancelled) {
          return;
        }

        if (error instanceof ApiError && error.status === 404) {
          setLoadState("not-found");
        } else {
          setLoadState("error");
        }
      }
    }

    void loadProduct();

    return () => {
      cancelled = true;
    };
  }, [productId, branchId]);

  const handleConfirmOrder = async () => {
    if (!product || !branchId) {
      return;
    }

    setOrderState("submitting");
    setOrderError(null);

    try {
      const response = await createOrder({
        commerce_branch_id: branchId,
        items: [{ product_id: product.id, quantity }],
      });

      setCreatedOrder(response.data);
      setOrderState("created");
    } catch (error) {
      if (isInsufficientStockError(error)) {
        setOrderError("No hay suficiente disponibilidad para la cantidad solicitada.");
      } else if (error instanceof ApiError) {
        setOrderError(error.message);
      } else {
        setOrderError("No se pudo crear el pedido. Intenta de nuevo.");
      }

      setOrderState("error");
    }
  };

  const handlePay = async () => {
    if (!createdOrder) {
      return;
    }

    setPayState("paying");
    setPayError(null);

    try {
      const result = await payOrder(
        createdOrder.id,
        selectedPaymentId === "reject" ? { simulate: "reject" } : {}
      );

      if (result.approved) {
        setTransaction(result.transaction);
        setPayState("approved");
      } else {
        setTransaction(result.transaction);
        setPayError(result.reason);
        setPayState("rejected");
      }
    } catch (error) {
      setPayError(error instanceof ApiError ? error.message : "No se pudo procesar el pago. Intenta de nuevo.");
      setPayState("error");
    }
  };

  if (!productId) {
    return (
      <section className="flex min-h-[60vh] flex-col items-center justify-center gap-3 px-4 text-center">
        <AlertCircle className="h-8 w-8 text-[var(--color-app-text-secondary-purple)]" />
        <h1 className="text-lg text-[var(--color-app-text-dark)]">Producto no encontrado</h1>
        <Link
          href="/app/discover"
          className="mt-2 inline-flex h-9 items-center rounded-xl border border-[var(--color-app-ui-divider)] px-3 text-xs text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)]"
        >
          Volver a Descubre
        </Link>
      </section>
    );
  }

  if (!branchId) {
    return (
      <section className="flex min-h-[60vh] flex-col items-center justify-center gap-3 px-4 text-center">
        <AlertCircle className="h-8 w-8 text-[var(--color-app-text-secondary-purple)]" />
        <h1 className="text-lg text-[var(--color-app-text-dark)]">No pudimos determinar la sucursal</h1>
        <p className="text-sm text-[var(--color-app-text-secondary-purple)]">
          Vuelve a intentar desde Descubre para completar tu pedido.
        </p>
        <Link
          href="/app/discover"
          className="mt-2 inline-flex h-9 items-center rounded-xl border border-[var(--color-app-ui-divider)] px-3 text-xs text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)]"
        >
          Volver a Descubre
        </Link>
      </section>
    );
  }

  if (loadState === "error") {
    return (
      <section className="flex min-h-[60vh] flex-col items-center justify-center gap-3 px-4 text-center">
        <AlertCircle className="h-8 w-8 text-[var(--color-app-text-secondary-purple)]" />
        <p className="text-sm text-[var(--color-app-text-secondary-purple)]">
          No se pudo cargar tu pedido en este momento. Intenta de nuevo.
        </p>
      </section>
    );
  }

  if (loadState === "not-found") {
    return (
      <section className="flex min-h-[60vh] flex-col items-center justify-center gap-3 px-4 text-center">
        <AlertCircle className="h-8 w-8 text-[var(--color-app-text-secondary-purple)]" />
        <h1 className="text-lg text-[var(--color-app-text-dark)]">Producto no encontrado</h1>
        <Link
          href="/app/discover"
          className="mt-2 inline-flex h-9 items-center rounded-xl border border-[var(--color-app-ui-divider)] px-3 text-xs text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)]"
        >
          Volver a Descubre
        </Link>
      </section>
    );
  }

  if (loadState === "loading" || !product) {
    return (
      <section className="flex min-h-[60vh] items-center justify-center px-4">
        <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Cargando tu pedido...</p>
      </section>
    );
  }

  // ============================================
  // Pantalla de éxito: pago aprobado
  // ============================================
  if (payState === "approved" && createdOrder && transaction) {
    return (
      <section className="flex min-h-[70vh] flex-col items-center justify-center gap-4 px-4 text-center">
        <span className="flex h-16 w-16 items-center justify-center rounded-full bg-[var(--color-app-tomatillo-soft)]">
          <CheckCircle2 className="h-9 w-9 text-[var(--color-app-status-success)]" aria-hidden="true" />
        </span>

        <div>
          <h1 className="text-xl text-[var(--color-app-text-dark)]">¡Pago aprobado!</h1>
          <p className="mt-1 text-sm text-[var(--color-app-text-secondary-purple)]">
            Tu pedido quedó confirmado y el comercio lo empezará a preparar.
          </p>
        </div>

        <div className="app-surface w-full max-w-sm p-4 text-left">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Resumen</h2>
          <dl className="mt-3 space-y-2 text-sm">
            <div className="flex items-center justify-between">
              <dt className="text-[var(--color-app-text-secondary-purple)]">Pedido</dt>
              <dd className="text-[var(--color-app-text-dark)]">#{createdOrder.id}</dd>
            </div>
            <div className="flex items-center justify-between">
              <dt className="text-[var(--color-app-text-secondary-purple)]">Producto</dt>
              <dd className="text-[var(--color-app-text-dark)]">{product.title}</dd>
            </div>
            <div className="flex items-center justify-between">
              <dt className="text-[var(--color-app-text-secondary-purple)]">Total pagado</dt>
              <dd className="text-[var(--color-app-text-dark)]">
                {formatPrice(transaction.amount)} {transaction.currency}
              </dd>
            </div>
            <div className="flex items-center justify-between gap-4">
              <dt className="text-[var(--color-app-text-secondary-purple)]">Referencia</dt>
              <dd className="truncate text-xs text-[var(--color-app-text-secondary-purple)]">
                {transaction.external_id ?? "—"}
              </dd>
            </div>
          </dl>
        </div>

        <Link href="/app/discover" className="app-btn-primary w-full max-w-sm">
          Volver a Descubre
        </Link>
      </section>
    );
  }

  const subtotal = product.price * quantity;
  const orderCreated = orderState === "created" && createdOrder !== null;
  const isBusy = orderState === "submitting" || payState === "paying";

  return (
    <section className="px-4 pb-6 pt-4">
      <header className="app-page-header">
        <div className="flex items-center gap-3">
          <Link
            href={`/app/product/${product.id}?branchId=${branchId}`}
            className="app-btn-icon app-header-back-button"
            aria-label="Volver a producto"
          >
            <ArrowLeft className="h-5 w-5" />
          </Link>

          <div>
            <h1 className="text-xl text-[var(--color-app-text-dark)]">Tu pedido</h1>
            <p className="text-sm text-[var(--color-app-text-secondary-purple)]">
              {orderCreated ? "Confirma el pago para completar tu compra" : "Paso previo al pago"}
            </p>
          </div>
        </div>
      </header>

      <div className="mt-4 space-y-4">
        <article className="app-surface p-4">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Resumen</h2>
          <p className="mt-2 text-sm text-[var(--color-app-text-secondary-purple)]">{product.title}</p>
          <p className="text-sm text-[var(--color-app-text-secondary-purple)]">{product.category}</p>

          <div className="mt-3 flex items-center justify-between">
            <span className="text-sm text-[var(--color-app-text-secondary-purple)]">Cantidad</span>
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => setQuantity((prev) => Math.max(1, prev - 1))}
                disabled={orderCreated || isBusy}
                className="flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--color-app-ui-divider)] text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)] disabled:cursor-not-allowed disabled:opacity-60"
                aria-label="Disminuir cantidad"
              >
                -
              </button>
              <span className="min-w-6 text-center text-sm text-[var(--color-app-text-dark)]">{quantity}</span>
              <button
                type="button"
                onClick={() => setQuantity((prev) => Math.min(product.quantityAvailable, prev + 1))}
                disabled={orderCreated || isBusy}
                className="flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--color-app-ui-divider)] text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)] disabled:cursor-not-allowed disabled:opacity-60"
                aria-label="Aumentar cantidad"
              >
                +
              </button>
            </div>
          </div>
        </article>

        <article className="app-surface p-4">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Entrega</h2>
          <div className="mt-3 flex items-start gap-2 rounded-xl border border-[var(--color-app-ui-divider)] p-3">
            <Store className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
            <p className="text-sm text-[var(--color-app-text-dark)]">Recoger en sucursal</p>
          </div>
        </article>

        <article className="app-surface p-4">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Metodo de pago</h2>

          <div className="mt-3 space-y-2">
            {SIMULATED_PAYMENT_OPTIONS.map((method) => {
              const checked = selectedPaymentId === method.id;
              const Icon = method.id === "reject" ? FlaskConical : CreditCard;

              return (
                <button
                  key={method.id}
                  type="button"
                  onClick={() => setSelectedPaymentId(method.id)}
                  disabled={isBusy}
                  className={`w-full rounded-xl border p-3 text-left disabled:cursor-not-allowed disabled:opacity-60 ${
                    checked
                      ? "border-[var(--color-app-text-primary-purple)] bg-[var(--color-app-tomatillo-soft)]"
                      : "border-[var(--color-app-ui-divider)]"
                  }`}
                >
                  <div className="flex items-center gap-3">
                    <span
                      className={`h-4 w-4 rounded-full border ${
                        checked
                          ? "border-[var(--color-app-text-primary-purple)] bg-[var(--color-app-text-primary-purple)]"
                          : "border-[var(--color-app-ui-divider)]"
                      }`}
                      aria-hidden="true"
                    />
                    <Icon className="h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
                    <div className="flex-1">
                      <p className="text-sm text-[var(--color-app-text-dark)]">{method.label}</p>
                      <p className="text-xs text-[var(--color-app-text-secondary-purple)]">{method.detail}</p>
                    </div>
                    {method.isDefault && (
                      <span className="rounded-full bg-[var(--color-app-text-primary-purple)] px-2 py-1 text-[10px] text-white">
                        Principal
                      </span>
                    )}
                  </div>
                </button>
              );
            })}
          </div>
        </article>

        <article className="app-surface p-4">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Total</h2>
          <div className="mt-3 space-y-2 text-sm text-[var(--color-app-text-secondary-purple)]">
            <div className="flex items-center justify-between border-t border-[var(--color-app-ui-divider)] pt-2 text-base text-[var(--color-app-text-dark)]">
              <span>Total</span>
              <span>{formatPrice(subtotal)}</span>
            </div>
          </div>
        </article>

        {!orderCreated ? (
          <>
            {orderState === "error" && orderError && (
              <div className="app-surface-outlined p-3 text-xs text-red-600" role="alert">
                <div className="flex items-start gap-2">
                  <AlertCircle className="mt-0.5 h-4 w-4" />
                  <p>{orderError}</p>
                </div>
              </div>
            )}

            <button
              type="button"
              onClick={handleConfirmOrder}
              disabled={orderState === "submitting"}
              className="app-btn-primary w-full gap-2 disabled:cursor-not-allowed disabled:opacity-60"
            >
              <CheckCircle2 className="h-4 w-4" />
              {orderState === "submitting" ? "Creando pedido..." : "Confirmar pedido"}
            </button>
          </>
        ) : (
          <>
            <div className="app-surface-outlined p-3 text-xs text-[var(--color-app-text-secondary-purple)]">
              <div className="flex items-start gap-2">
                <CheckCircle2 className="mt-0.5 h-4 w-4 text-[var(--color-app-status-success)]" />
                <p>
                  Pedido #{createdOrder.id} creado. Confirma el pago para que el comercio lo prepare.
                </p>
              </div>
            </div>

            {(payState === "rejected" || payState === "error") && payError && (
              <div className="app-surface-outlined p-3 text-xs text-red-600" role="alert">
                <div className="flex items-start gap-2">
                  <AlertCircle className="mt-0.5 h-4 w-4" />
                  <div>
                    <p>{payError}</p>
                    {payState === "rejected" && (
                      <p className="mt-1 text-[var(--color-app-text-secondary-purple)]">
                        Puedes intentarlo de nuevo con otro método de pago.
                      </p>
                    )}
                  </div>
                </div>
              </div>
            )}

            <button
              type="button"
              onClick={handlePay}
              disabled={payState === "paying"}
              className="app-btn-primary w-full gap-2 disabled:cursor-not-allowed disabled:opacity-60"
            >
              <CreditCard className="h-4 w-4" />
              {payState === "paying"
                ? "Procesando pago..."
                : payState === "rejected" || payState === "error"
                  ? "Reintentar pago"
                  : `Pagar ${formatPrice(createdOrder.total_price)}`}
            </button>
          </>
        )}
      </div>
    </section>
  );
}
