"use client";

import Link from "next/link";
import { useMemo, useState } from "react";
import { CheckCircle2, ChevronRight, Clock, MapPin } from "lucide-react";

type OrdersTab = "active" | "history";

type AppOrder = {
  id: number;
  storeId: number;
  code: string;
  storeName: string;
  bagType: string;
  price: number;
  dateLabel: string;
  statusLabel: string;
  statusTone: "primary" | "muted";
  deliveryType: "pickup" | "delivery";
  pickupTime?: string;
  address?: string;
};

const ACTIVE_ORDERS: AppOrder[] = [
  {
    id: 1,
    storeId: 1,
    code: "#A2847",
    storeName: "Panaderia El Trigal",
    bagType: "Bolsa sorpresa",
    price: 8000,
    dateLabel: "Hoy",
    statusLabel: "Listo para recoger",
    statusTone: "primary",
    deliveryType: "pickup",
    pickupTime: "6:00 p.m. - 7:00 p.m.",
    address: "Calle 72 #10-34, Chapinero, Bogota",
  },
];

const HISTORY_ORDERS: AppOrder[] = [
  {
    id: 2,
    storeId: 2,
    code: "#A2846",
    storeName: "Cafe Amor Perfecto",
    bagType: "Bolsa sorpresa",
    price: 9500,
    dateLabel: "Ayer, 7:15 p.m.",
    statusLabel: "Entregado",
    statusTone: "muted",
    deliveryType: "delivery",
  },
  {
    id: 3,
    storeId: 3,
    code: "#A2845",
    storeName: "Restaurante Sabor Local",
    bagType: "Bolsa sorpresa",
    price: 12000,
    dateLabel: "3 nov, 6:45 p.m.",
    statusLabel: "Entregado",
    statusTone: "muted",
    deliveryType: "pickup",
  },
];

function formatPrice(value: number): string {
  return `$${value.toLocaleString("es-CO")}`;
}

export default function AppOrdersPage() {
  const [activeTab, setActiveTab] = useState<OrdersTab>("active");

  const currentOrders = useMemo(() => {
    return activeTab === "active" ? ACTIVE_ORDERS : HISTORY_ORDERS;
  }, [activeTab]);

  return (
    <section className="px-4 pb-6 pt-4">
      <header className="rounded-2xl bg-[var(--color-app-ui-background)] px-4 py-4 shadow-[var(--app-shadow-card)]">
        <h1 className="text-2xl text-[var(--color-app-text-dark)]">Mis pedidos</h1>
        <p className="mt-1 text-sm text-[var(--color-app-text-secondary-purple)]">Tus rescates de comida</p>
      </header>

      <div className="mt-4 rounded-2xl bg-[var(--color-app-ui-background)] px-4 py-3 shadow-[var(--app-shadow-card)]">
        <div className="flex items-center gap-5">
          <button
            type="button"
            className={`border-b-2 pb-2 text-sm ${
              activeTab === "active"
                ? "border-[var(--color-app-text-primary-purple)] text-[var(--color-app-text-primary-purple)]"
                : "border-transparent text-[var(--color-app-text-secondary-purple)]"
            }`}
            onClick={() => setActiveTab("active")}
          >
            Pedidos en curso
          </button>

          <button
            type="button"
            className={`border-b-2 pb-2 text-sm ${
              activeTab === "history"
                ? "border-[var(--color-app-text-primary-purple)] text-[var(--color-app-text-primary-purple)]"
                : "border-transparent text-[var(--color-app-text-secondary-purple)]"
            }`}
            onClick={() => setActiveTab("history")}
          >
            Historial
          </button>
        </div>
      </div>

      <div className="mt-4 space-y-3">
        {currentOrders.length === 0 && (
          <div className="rounded-2xl bg-[var(--color-app-ui-background)] p-4 text-sm text-[var(--color-app-text-secondary-purple)] shadow-[var(--app-shadow-card)]">
            No tienes pedidos en esta seccion.
          </div>
        )}

        {activeTab === "active" &&
          currentOrders.map((order) => (
            <article
              key={order.id}
              className="overflow-hidden rounded-2xl bg-[var(--color-app-ui-background)] shadow-[var(--app-shadow-card)]"
            >
              <div className="flex items-center justify-between border-b border-[var(--color-app-ui-divider)] px-4 py-3">
                <span className="text-sm text-[var(--color-app-text-secondary-purple)]">Pedido {order.code}</span>
                <span className="rounded-full bg-[var(--color-app-tomatillo-soft)] px-3 py-1 text-xs text-[var(--color-app-text-primary-purple)]">
                  {order.statusLabel}
                </span>
              </div>

              <div className="space-y-3 px-4 py-4">
                <div>
                  <h2 className="text-base text-[var(--color-app-text-dark)]">{order.storeName}</h2>
                  <p className="text-sm text-[var(--color-app-text-secondary-purple)]">{order.bagType}</p>
                  <p className="mt-1 text-lg text-[var(--color-app-tomatillo-medium)]">{formatPrice(order.price)}</p>
                </div>

                {order.deliveryType === "pickup" && (
                  <div className="rounded-xl bg-[var(--color-app-ui-background-soft)] p-3">
                    <h3 className="text-sm text-[var(--color-app-text-dark)]">Informacion de recogida</h3>
                    <div className="mt-2 space-y-2 text-sm text-[var(--color-app-text-secondary-purple)]">
                      {order.pickupTime && (
                        <div className="flex items-start gap-2">
                          <Clock className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
                          <span>{order.pickupTime}</span>
                        </div>
                      )}
                      {order.address && (
                        <div className="flex items-start gap-2">
                          <MapPin className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
                          <span>{order.address}</span>
                        </div>
                      )}
                    </div>
                  </div>
                )}
              </div>
            </article>
          ))}

        {activeTab === "history" &&
          currentOrders.map((order) => (
            <Link
              href={`/app/store/${order.storeId}`}
              key={order.id}
              className="flex w-full items-center gap-3 rounded-2xl bg-[var(--color-app-ui-background)] px-4 py-4 text-left shadow-[var(--app-shadow-card)]"
            >
              <div className="h-14 w-14 rounded-xl bg-[var(--color-app-ui-background-soft)]" />

              <div className="min-w-0 flex-1">
                <h2 className="truncate text-sm text-[var(--color-app-text-dark)]">{order.storeName}</h2>
                <p className="text-xs text-[var(--color-app-text-secondary-purple)]">{order.bagType}</p>
                <p className="mt-1 text-xs text-[var(--color-app-text-secondary-purple)]">{order.dateLabel}</p>
              </div>

              <div className="text-right">
                <p className="text-sm text-[var(--color-app-text-dark)]">{formatPrice(order.price)}</p>
                <div className="mt-1 flex items-center justify-end gap-1 text-xs text-[var(--color-app-status-success)]">
                  <CheckCircle2 className="h-3 w-3" />
                  <span>{order.statusLabel}</span>
                </div>
              </div>

              <ChevronRight className="h-4 w-4 text-[var(--color-app-text-secondary-purple)]" />
            </Link>
          ))}
      </div>
    </section>
  );
}
