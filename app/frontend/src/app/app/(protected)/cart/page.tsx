"use client";

import Link from "next/link";
import { useState } from "react";
import { useSearchParams } from "next/navigation";
import { AlertCircle, ArrowLeft, CheckCircle2, CreditCard, Store, Truck } from "lucide-react";
import { getStoreById } from "@/lib/app/mock-catalog";

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

type DeliveryOption = "pickup" | "delivery";

function formatPrice(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

export default function AppCartPage() {
  const searchParams = useSearchParams();
  const storeIdRaw = searchParams.get("storeId");
  const parsedStoreId = storeIdRaw ? Number.parseInt(storeIdRaw, 10) : 1;
  const safeStoreId = Number.isNaN(parsedStoreId) ? 1 : parsedStoreId;

  const store = getStoreById(safeStoreId) ?? getStoreById(1);
  const maxAvailable = store?.available ?? 1;
  const storePrice = store?.price ?? 0;
  const storeDeliveryCost = store?.deliveryCost ?? 0;

  const [quantity, setQuantity] = useState(1);
  const [deliveryOption, setDeliveryOption] = useState<DeliveryOption>("pickup");
  const [selectedPaymentId, setSelectedPaymentId] = useState<string>(SAVED_PAYMENT_METHODS[0].id);

  const subtotal = storePrice * quantity;
  const deliveryFee = deliveryOption === "delivery" ? storeDeliveryCost : 0;
  const total = subtotal + deliveryFee;

  if (!store) {
    return null;
  }

  return (
    <section className="px-4 pb-6 pt-4">
      <header className="app-page-header">
        <div className="flex items-center gap-3">
          <Link
            href={`/app/product/${store.id}`}
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
          <p className="mt-2 text-sm text-[var(--color-app-text-secondary-purple)]">{store.name}</p>
          <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Bolsa sorpresa de {store.category.toLowerCase()}</p>

          <div className="mt-3 flex items-center justify-between">
            <span className="text-sm text-[var(--color-app-text-secondary-purple)]">Cantidad</span>
            <div className="flex items-center gap-2">
              <button
                type="button"
                onClick={() => setQuantity((prev) => Math.max(1, prev - 1))}
                className="flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--color-app-ui-divider)] text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)]"
                aria-label="Disminuir cantidad"
              >
                -
              </button>
              <span className="min-w-6 text-center text-sm text-[var(--color-app-text-dark)]">{quantity}</span>
              <button
                type="button"
                onClick={() => setQuantity((prev) => Math.min(maxAvailable, prev + 1))}
                className="flex h-9 w-9 items-center justify-center rounded-xl border border-[var(--color-app-ui-divider)] text-[var(--color-app-text-primary-purple)] transition hover:bg-[var(--color-app-ui-background-soft)]"
                aria-label="Aumentar cantidad"
              >
                +
              </button>
            </div>
          </div>
        </article>

        <article className="app-surface p-4">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Metodo de entrega</h2>

          <div className="mt-3 space-y-2">
            <button
              type="button"
              onClick={() => setDeliveryOption("pickup")}
              className={`w-full rounded-xl border p-3 text-left ${
                deliveryOption === "pickup"
                  ? "border-[var(--color-app-text-primary-purple)] bg-[var(--color-app-tomatillo-soft)]"
                  : "border-[var(--color-app-ui-divider)]"
              }`}
            >
              <div className="flex items-start gap-2">
                <Store className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
                <div>
                  <p className="text-sm text-[var(--color-app-text-dark)]">Recoger en tienda</p>
                  <p className="text-xs text-[var(--color-app-text-secondary-purple)]">{store.pickupTime}</p>
                </div>
              </div>
            </button>

            <button
              type="button"
              onClick={() => setDeliveryOption("delivery")}
              className={`w-full rounded-xl border p-3 text-left ${
                deliveryOption === "delivery"
                  ? "border-[var(--color-app-text-primary-purple)] bg-[var(--color-app-tomatillo-soft)]"
                  : "border-[var(--color-app-ui-divider)]"
              }`}
            >
              <div className="flex items-start gap-2">
                <Truck className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
                <div>
                  <p className="text-sm text-[var(--color-app-text-dark)]">Envio a domicilio</p>
                  <p className="text-xs text-[var(--color-app-text-secondary-purple)]">
                    {store.deliveryTime} · {formatPrice(store.deliveryCost)}
                  </p>
                </div>
              </div>
            </button>
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
            <div className="flex items-center justify-between">
              <span>Subtotal</span>
              <span>{formatPrice(subtotal)}</span>
            </div>
            <div className="flex items-center justify-between">
              <span>Entrega</span>
              <span>{deliveryFee === 0 ? "Gratis" : formatPrice(deliveryFee)}</span>
            </div>
            <div className="flex items-center justify-between border-t border-[var(--color-app-ui-divider)] pt-2 text-base text-[var(--color-app-text-dark)]">
              <span>Total</span>
              <span>{formatPrice(total)}</span>
            </div>
          </div>
        </article>

        <div className="app-surface-outlined p-3 text-xs text-[var(--color-app-text-secondary-purple)]">
          <div className="flex items-start gap-2">
            <AlertCircle className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
            <p>
              Flujo disponible hasta seleccion del metodo de pago. La confirmacion y el procesamiento de pago se
              habilitaran cuando se defina el proveedor.
            </p>
          </div>
        </div>

        <button
          type="button"
          disabled
          className="app-btn-primary w-full gap-2"
        >
          <CheckCircle2 className="h-4 w-4" />
          Confirmacion de pago no disponible aún
        </button>
      </div>
    </section>
  );
}
