"use client";

import Link from "next/link";
import { useMemo, useState } from "react";
import { useSearchParams } from "next/navigation";
import { ChevronLeft, MessageCircle, Plus } from "lucide-react";
import { SUPPORT_TICKETS, type SupportTicketStatus } from "@/lib/app/support-mock";

type SupportTab = "active" | "history";

function statusLabel(status: SupportTicketStatus): string {
  if (status === "open") return "Abierto";
  if (status === "in-progress") return "En progreso";
  if (status === "resolved") return "Resuelto";
  return "Cerrado";
}

function statusStyles(status: SupportTicketStatus): string {
  if (status === "open") return "bg-orange-100 text-orange-700";
  if (status === "in-progress") return "bg-blue-100 text-blue-700";
  if (status === "resolved") return "bg-[var(--color-app-tomatillo-soft)] text-[var(--color-app-text-primary-purple)]";
  return "bg-gray-100 text-gray-600";
}

export default function AppSupportPage() {
  const [activeTab, setActiveTab] = useState<SupportTab>("active");
  const searchParams = useSearchParams();
  const wasCreated = searchParams.get("created") === "1";

  const activeTickets = useMemo(
    () => SUPPORT_TICKETS.filter((ticket) => ticket.status === "open" || ticket.status === "in-progress"),
    []
  );

  const historyTickets = useMemo(
    () => SUPPORT_TICKETS.filter((ticket) => ticket.status === "resolved" || ticket.status === "closed"),
    []
  );

  const displayTickets = activeTab === "active" ? activeTickets : historyTickets;

  return (
    <section className="px-4 pb-6 pt-4">
      <header className="rounded-2xl bg-[var(--color-app-ui-background)] px-4 py-4 shadow-[var(--app-shadow-card)]">
        <div className="flex items-center gap-3">
          <Link
            href="/app/profile"
            className="rounded-xl bg-[var(--color-app-ui-background-soft)] p-2 text-[var(--color-app-text-primary-purple)]"
            aria-label="Volver a perfil"
          >
            <ChevronLeft className="h-5 w-5" />
          </Link>

          <div>
            <h1 className="text-xl text-[var(--color-app-text-dark)]">Soporte</h1>
            <p className="text-sm text-[var(--color-app-text-secondary-purple)]">Solicitudes y seguimiento</p>
          </div>
        </div>
      </header>

      {wasCreated && (
        <div className="mt-4 rounded-xl bg-[var(--color-app-tomatillo-soft)] px-3 py-2 text-sm text-[var(--color-app-text-primary-purple)]">
          Solicitud creada correctamente. Nuestro equipo te respondera en breve.
        </div>
      )}

      <div className="mt-4 rounded-2xl bg-[var(--color-app-ui-background)] px-4 py-3 shadow-[var(--app-shadow-card)]">
        <div className="flex items-center gap-4">
          <button
            type="button"
            className={`border-b-2 pb-2 text-sm ${
              activeTab === "active"
                ? "border-[var(--color-app-text-primary-purple)] text-[var(--color-app-text-primary-purple)]"
                : "border-transparent text-[var(--color-app-text-secondary-purple)]"
            }`}
            onClick={() => setActiveTab("active")}
          >
            Activas ({activeTickets.length})
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
            Historial ({historyTickets.length})
          </button>
        </div>
      </div>

      <div className="mt-4 space-y-3">
        {displayTickets.length === 0 && (
          <div className="rounded-2xl bg-[var(--color-app-ui-background)] p-6 text-center shadow-[var(--app-shadow-card)]">
            <MessageCircle className="mx-auto h-8 w-8 text-[var(--color-app-text-secondary-purple)]" />
            <p className="mt-2 text-sm text-[var(--color-app-text-secondary-purple)]">No hay solicitudes para esta seccion.</p>
          </div>
        )}

        {displayTickets.map((ticket) => (
          <Link
            key={ticket.id}
            href={`/app/support/ticket/${ticket.id}`}
            className="block rounded-2xl bg-[var(--color-app-ui-background)] p-4 shadow-[var(--app-shadow-card)]"
          >
            <div className="flex items-start justify-between gap-2">
              <div>
                <h2 className="text-sm text-[var(--color-app-text-dark)]">{ticket.title}</h2>
                <p className="mt-1 text-xs text-[var(--color-app-text-secondary-purple)]">
                  #{ticket.id} • {ticket.category}
                  {ticket.orderId ? ` • ${ticket.orderId}` : ""}
                </p>
                <p className="mt-1 text-xs text-[var(--color-app-text-secondary-purple)]">
                  {ticket.createdAt} • {ticket.lastUpdate}
                </p>
              </div>
              <span className={`rounded-full px-3 py-1 text-xs ${statusStyles(ticket.status)}`}>
                {statusLabel(ticket.status)}
              </span>
            </div>
          </Link>
        ))}
      </div>

      <div className="mt-4">
        <Link
          href="/app/support/create-ticket"
          className="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-[var(--color-app-text-primary-purple)] px-4 py-3 text-sm text-white"
        >
          <Plus className="h-4 w-4" />
          Crear solicitud
        </Link>
      </div>
    </section>
  );
}
