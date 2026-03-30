"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useState } from "react";
import { AlertCircle, ChevronLeft, CreditCard, HelpCircle, Package, Smartphone } from "lucide-react";

type TicketCategory = "order" | "payment" | "technical" | "other";

type CategoryConfig = {
  id: TicketCategory;
  label: string;
  description: string;
};

const CATEGORY_OPTIONS: CategoryConfig[] = [
  {
    id: "order",
    label: "Problema con pedido",
    description: "Pedido no recogido, producto diferente, etc.",
  },
  {
    id: "payment",
    label: "Problema con pago",
    description: "Cobros, reembolsos o metodos de pago",
  },
  {
    id: "technical",
    label: "Problema tecnico",
    description: "Errores en la app o fallos tecnicos",
  },
  {
    id: "other",
    label: "Otro",
    description: "Consultas generales o sugerencias",
  },
];

function CategoryIcon({ category }: { category: TicketCategory }) {
  if (category === "order") return <Package className="h-5 w-5" />;
  if (category === "payment") return <CreditCard className="h-5 w-5" />;
  if (category === "technical") return <Smartphone className="h-5 w-5" />;
  return <HelpCircle className="h-5 w-5" />;
}

export default function AppCreateTicketPage() {
  const router = useRouter();
  const [category, setCategory] = useState<TicketCategory | null>(null);
  const [relatedOrder, setRelatedOrder] = useState("");
  const [description, setDescription] = useState("");

  const canSubmit = category !== null && description.trim().length >= 10;

  const handleSubmit = () => {
    if (!canSubmit) {
      return;
    }

    router.push("/app/support?created=1");
  };

  return (
    <section className="px-4 pb-6 pt-4">
      <header className="rounded-2xl bg-[var(--color-app-ui-background)] px-4 py-4 shadow-[var(--app-shadow-card)]">
        <div className="flex items-center gap-3">
          <Link
            href="/app/support"
            className="rounded-xl bg-[var(--color-app-ui-background-soft)] p-2 text-[var(--color-app-text-primary-purple)]"
            aria-label="Volver a soporte"
          >
            <ChevronLeft className="h-5 w-5" />
          </Link>
          <div>
            <h1 className="text-xl text-[var(--color-app-text-dark)]">Nueva solicitud</h1>
            <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Describe tu problema</p>
          </div>
        </div>
      </header>

      <div className="mt-4 space-y-4">
        <article className="rounded-2xl bg-[var(--color-app-ui-background)] p-4 shadow-[var(--app-shadow-card)]">
          <h2 className="text-base text-[var(--color-app-text-dark)]">Categoria</h2>
          <div className="mt-3 space-y-2">
            {CATEGORY_OPTIONS.map((option) => {
              const selected = category === option.id;

              return (
                <button
                  key={option.id}
                  type="button"
                  onClick={() => setCategory(option.id)}
                  className={`w-full rounded-xl border p-3 text-left ${
                    selected
                      ? "border-[var(--color-app-text-primary-purple)] bg-[var(--color-app-tomatillo-soft)]"
                      : "border-[var(--color-app-ui-divider)]"
                  }`}
                >
                  <div className="flex items-start gap-2">
                    <div className="mt-0.5 text-[var(--color-app-text-primary-purple)]">
                      <CategoryIcon category={option.id} />
                    </div>
                    <div>
                      <p className="text-sm text-[var(--color-app-text-dark)]">{option.label}</p>
                      <p className="text-xs text-[var(--color-app-text-secondary-purple)]">{option.description}</p>
                    </div>
                  </div>
                </button>
              );
            })}
          </div>
        </article>

        {(category === "order" || category === "payment") && (
          <article className="rounded-2xl bg-[var(--color-app-ui-background)] p-4 shadow-[var(--app-shadow-card)]">
            <label className="text-sm text-[var(--color-app-text-dark)]" htmlFor="relatedOrder">
              Pedido relacionado (opcional)
            </label>
            <input
              id="relatedOrder"
              type="text"
              value={relatedOrder}
              onChange={(event) => setRelatedOrder(event.target.value)}
              placeholder="Ej: #ORD-4521"
              className="mt-2 w-full rounded-xl border border-[var(--color-app-ui-divider)] px-3 py-2 text-sm text-[var(--color-app-text-dark)] outline-none"
            />
          </article>
        )}

        <article className="rounded-2xl bg-[var(--color-app-ui-background)] p-4 shadow-[var(--app-shadow-card)]">
          <label className="text-sm text-[var(--color-app-text-dark)]" htmlFor="ticketDescription">
            Descripcion del problema
          </label>
          <textarea
            id="ticketDescription"
            value={description}
            onChange={(event) => setDescription(event.target.value)}
            placeholder="Por favor describe tu problema con el mayor detalle posible..."
            className="mt-2 min-h-[140px] w-full rounded-xl border border-[var(--color-app-ui-divider)] px-3 py-2 text-sm text-[var(--color-app-text-dark)] outline-none"
          />
          <p className="mt-2 text-xs text-[var(--color-app-text-secondary-purple)]">Minimo 10 caracteres ({description.length}/10)</p>
        </article>

        <div className="rounded-2xl border border-[var(--color-app-ui-divider)] bg-[var(--color-app-ui-background)] p-3 text-xs text-[var(--color-app-text-secondary-purple)]">
          <div className="flex items-start gap-2">
            <AlertCircle className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
            <p>Tiempo estimado de respuesta: hasta 24 horas habiles.</p>
          </div>
        </div>

        <button
          type="button"
          onClick={handleSubmit}
          disabled={!canSubmit}
          className={`h-12 w-full rounded-xl text-sm ${
            canSubmit
              ? "bg-[var(--color-app-text-primary-purple)] text-white"
              : "bg-[var(--color-app-ui-divider)] text-[var(--color-app-text-secondary-purple)]"
          }`}
        >
          Enviar solicitud
        </button>
      </div>
    </section>
  );
}
