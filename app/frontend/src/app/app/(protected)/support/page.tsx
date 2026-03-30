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
      <header className="app-page-header">
        <div className="flex items-center gap-3">
          <Link
            href="/app/profile"
            className="app-btn-icon app-header-back-button"
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
        <div className="app-surface-soft mt-4 px-3 py-2 text-sm text-[var(--color-app-text-primary-purple)]">
          Solicitud creada correctamente. Nuestro equipo te respondera en breve.
        </div>
      )}

      <div className="app-surface mt-4 px-4 py-3">
        <div className="flex items-center gap-4">
          <button
            type="button"
            className={`app-segmented-tab ${activeTab === "active" ? "is-active" : ""}`}
            onClick={() => setActiveTab("active")}
          >
            Activas ({activeTickets.length})
          </button>
          <button
            type="button"
            className={`app-segmented-tab ${activeTab === "history" ? "is-active" : ""}`}
            onClick={() => setActiveTab("history")}
          >
            Historial ({historyTickets.length})
          </button>
        </div>
      </div>

      <div className="mt-4 space-y-3">
        {displayTickets.length === 0 && (
          <div className="app-surface p-6 text-center">
            <MessageCircle className="mx-auto h-8 w-8 text-[var(--color-app-text-secondary-purple)]" />
            <p className="mt-2 text-sm text-[var(--color-app-text-secondary-purple)]">No hay solicitudes para esta seccion.</p>
          </div>
        )}

        {displayTickets.map((ticket) => (
          <Link
            key={ticket.id}
            href={`/app/support/ticket/${ticket.id}`}
            className="app-card-action app-surface block p-4"
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
          className="app-btn-primary w-full gap-2"
        >
          <Plus className="h-4 w-4" />
          Crear solicitud
        </Link>
      </div>
    </section>
  );
}
