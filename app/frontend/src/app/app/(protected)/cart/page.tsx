"use client";

import Link from "next/link";
import { useEffect, useState } from "react";
import { useSearchParams } from "next/navigation";
import { AlertCircle, ArrowLeft, CheckCircle2, CreditCard, Store } from "lucide-react";
import { ApiError } from "@/lib/api/client";
import { getProductDetail } from "@/lib/api/app-catalog";
import { createOrder, isInsufficientStockError } from "@/lib/api/app-orders";
import { mapProductDetailToView, type ProductDetailView } from "@/types/app-catalog.adapters";
import type { AppOrder } from "@/types/app-orders";

const SAVED_PAYMENT_METHODS = [
  {
    id: "card-default",
    label: "Pago con tarjeta debito o credito",
    detail: "**** 4532",
    isDefault: true,
  },
  {
    id: "cash",
    label: "Efectivo",
    detail: "Pago en tienda",
    isDefault: false,
  },
] as const;

type LoadState = "loading" | "not-found" | "error" | "loaded";
type OrderState = "idle" | "submitting" | "created" | "error";

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
  const [selectedPaymentId, setSelectedPaymentId] = useState<string>(SAVED_PAYMENT_METHODS[0].id);
  const [orderState, setOrderState] = useState<OrderState>("idle");
  const [orderError, setOrderError] = useState<string | null>(null);
  const [createdOrder, setCreatedOrder] = useState<AppOrder | null>(null);

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

  if (loadState === "loading" || !product) {
    return (
      <section className="flex min-h-[60vh] items-center justify-center px-4">
        <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Cargando tu pedido...</p>
      </section>
    );
  }

  const subtotal = product.price * quantity;

  return (
    <section className="px-4 pb-6 pt-4">
      <header className="app-page-header">
        <div className="flex items-center gap-3">
          <Link
            href={`/app/product/${product.id}`}
            className="app-btn-icon app-header-back-button"
            aria-label="Volver a producto"
          >
            <ArrowLeft className="h-5 w-5" />
          </Link>

          <div>
            <h1 className="text-xl text-[var(--color-app-text-dark)]">Tu pedido</h1>
            <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Paso previo al pago</p>
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
                disabled={orderState === "created"}
                className="flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--color-app-ui-divider)] text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)] disabled:cursor-not-allowed disabled:opacity-60"
                aria-label="Disminuir cantidad"
              >
                -
              </button>
              <span className="min-w-6 text-center text-sm text-[var(--color-app-text-dark)]">{quantity}</span>
              <button
                type="button"
                onClick={() => setQuantity((prev) => Math.min(product.quantityAvailable, prev + 1))}
                disabled={orderState === "created"}
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
            {SAVED_PAYMENT_METHODS.map((method) => {
              const checked = selectedPaymentId === method.id;

              return (
                <button
                  key={method.id}
                  type="button"
                  onClick={() => setSelectedPaymentId(method.id)}
                  className={`w-full rounded-xl border p-3 text-left ${
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
                    <CreditCard className="h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
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

        {orderState === "created" && createdOrder ? (
          <>
            <div className="app-surface-outlined p-3 text-xs text-[var(--color-app-status-success)]">
              <div className="flex items-start gap-2">
                <CheckCircle2 className="mt-0.5 h-4 w-4" />
                <p>
                  Pedido #{createdOrder.id} creado, pendiente de pago. La confirmación y el procesamiento de pago se
                  habilitarán cuando se defina la pasarela.
                </p>
              </div>
            </div>

            <button type="button" disabled className="app-btn-primary w-full gap-2">
              <CheckCircle2 className="h-4 w-4" />
              Confirmacion de pago no disponible aún
            </button>
          </>
        ) : (
          <>
            {orderState === "error" && orderError && (
              <div className="app-surface-outlined p-3 text-xs text-red-600">
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
        )}
      </div>
    </section>
  );
}
