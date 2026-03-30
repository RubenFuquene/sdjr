"use client";

import Link from "next/link";
import { useParams } from "next/navigation";
import { useMemo, useState } from "react";
import { AlertCircle, CheckCircle2, ChevronLeft, MessageSquare, Send } from "lucide-react";
import { getMessagesByTicketId, getTicketById, type SupportMessage } from "@/lib/app/support-mock";

function statusStyles(status: string): string {
  if (status === "open") return "bg-orange-100 text-orange-700";
  if (status === "in-progress") return "bg-blue-100 text-blue-700";
  if (status === "resolved") return "bg-[var(--color-app-tomatillo-soft)] text-[var(--color-app-text-primary-purple)]";
  return "bg-gray-100 text-gray-600";
}

function statusLabel(status: string): string {
  if (status === "open") return "Abierto";
  if (status === "in-progress") return "En progreso";
  if (status === "resolved") return "Resuelto";
  return "Cerrado";
}

export default function AppTicketDetailPage() {
  const params = useParams<{ ticketId: string }>();
  const parsedTicketId = Number.parseInt(params.ticketId, 10);
  const ticket = Number.isNaN(parsedTicketId) ? null : getTicketById(parsedTicketId);

  const initialMessages = useMemo(() => {
    if (!ticket) {
      return [];
    }
    return getMessagesByTicketId(ticket.id);
  }, [ticket]);

  const [messages, setMessages] = useState<SupportMessage[]>(initialMessages);
  const [newMessage, setNewMessage] = useState("");

  if (!ticket) {
    return (
      <section className="px-4 pb-6 pt-4">
        <div className="app-surface p-4 text-sm text-[var(--color-app-text-secondary-purple)]">
          Ticket no encontrado.
        </div>
      </section>
    );
  }

  const isClosed = ticket.status === "closed";
  const isResolved = ticket.status === "resolved";

  const handleSendMessage = () => {
    if (!newMessage.trim() || isClosed) {
      return;
    }

    setMessages((prev) => [
      ...prev,
      {
        id: prev.length + 1,
        sender: "user",
        content: newMessage.trim(),
        timestamp: "Ahora",
      },
    ]);
    setNewMessage("");
  };

  return (
    <section className="px-4 pb-6 pt-4">
      <header className="app-page-header">
        <div className="flex items-center gap-3">
          <Link
            href="/app/support"
            className="app-btn-icon app-header-back-button"
            aria-label="Volver a soporte"
          >
            <ChevronLeft className="h-5 w-5" />
          </Link>
          <div>
            <h1 className="text-lg text-[var(--color-app-text-dark)]">Solicitud #{ticket.id}</h1>
            <p className="text-xs text-[var(--color-app-text-secondary-purple)]">{ticket.category}</p>
          </div>
          <span className={`ml-auto rounded-full px-3 py-1 text-xs ${statusStyles(ticket.status)}`}>
            {statusLabel(ticket.status)}
          </span>
        </div>
      </header>

      <div className="mt-4 space-y-3">
        {messages.map((message) => {
          const isUser = message.sender === "user";

          return (
            <div key={message.id} className={`flex ${isUser ? "justify-end" : "justify-start"}`}>
              <div
                className={`max-w-[80%] rounded-2xl px-3 py-2 text-sm ${
                  isUser
                    ? "bg-[var(--color-app-text-primary-purple)] text-white"
                    : "bg-[var(--color-app-ui-background)] text-[var(--color-app-text-dark)] shadow-[var(--app-shadow-card)]"
                }`}
              >
                <p>{message.content}</p>
                <p className={`mt-1 text-[10px] ${isUser ? "text-white/80" : "text-[var(--color-app-text-secondary-purple)]"}`}>
                  {message.timestamp}
                </p>
              </div>
            </div>
          );
        })}
      </div>

      {isResolved && (
        <div className="app-surface-outlined mt-4 p-3 text-xs text-[var(--color-app-text-secondary-purple)]">
          <div className="flex items-start gap-2">
            <CheckCircle2 className="mt-0.5 h-4 w-4 text-[var(--color-app-status-success)]" />
            <p>Tu caso aparece como resuelto. Puedes responder para reabrir la conversacion.</p>
          </div>
        </div>
      )}

      {isClosed && (
        <div className="app-surface-outlined mt-4 p-3 text-xs text-[var(--color-app-text-secondary-purple)]">
          <div className="flex items-start gap-2">
            <AlertCircle className="mt-0.5 h-4 w-4 text-[var(--color-app-text-primary-purple)]" />
            <p>Este ticket esta cerrado y no permite nuevos mensajes.</p>
          </div>
        </div>
      )}

      <div className="app-surface mt-4 p-3">
        <div className="flex items-end gap-2">
          <textarea
            value={newMessage}
            onChange={(event) => setNewMessage(event.target.value)}
            placeholder={isClosed ? "Ticket cerrado" : "Escribe tu mensaje..."}
            disabled={isClosed}
            className="min-h-[44px] flex-1 rounded-xl border border-[var(--color-app-ui-divider)] px-3 py-2 text-sm text-[var(--color-app-text-dark)] outline-none disabled:bg-[var(--color-app-ui-background-soft)]"
          />
          <button
            type="button"
            onClick={handleSendMessage}
            disabled={isClosed || newMessage.trim().length === 0}
            className="app-btn-primary app-btn-icon h-[52px] w-[52px]"
            aria-label="Enviar mensaje"
          >
            <Send className="h-4 w-4" />
          </button>
        </div>

        <div className="mt-2 text-[10px] text-[var(--color-app-text-secondary-purple)]">
          MVP: mensajes locales sin persistencia backend.
        </div>
      </div>
    </section>
  );
}
