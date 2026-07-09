'use client';

/**
 * Hilo de mensajes admin↔proveedor de la ruta de revisión.
 * Muestra los mensajes visibles (MS) y rechazos (RJ) del comercio del proveedor
 * y permite enviar mensajes mientras el comercio está en ruta de aprobación.
 */

import { useState, type FormEvent } from 'react';
import { Send, MessageSquare } from 'lucide-react';
import { useProviderMessages } from '@/hooks/provider/use-provider-messages';

function formatDate(value: string): string {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  return date.toLocaleString('es-CO', {
    day: '2-digit',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  });
}

export function ProviderMessageThread() {
  const {
    messages,
    ownerUserId,
    canMessage,
    isLoading,
    loadError,
    isSending,
    sendError,
    sendMessage,
  } = useProviderMessages();

  const [draft, setDraft] = useState('');

  const handleSubmit = async (event: FormEvent) => {
    event.preventDefault();
    const body = draft.trim();
    if (!body) return;
    try {
      await sendMessage(body);
      setDraft('');
    } catch {
      // El error ya queda en sendError; se conserva el borrador para reintentar.
    }
  };

  return (
    <div className="bg-white rounded-[18px] shadow-sm p-6 md:p-8">
      <div className="flex items-center gap-2 mb-4">
        <MessageSquare className="h-5 w-5 text-[#4B236A]" />
        <h2 className="text-lg font-semibold text-[#1A1A1A]">Mensajes con el equipo Ñapa</h2>
      </div>

      {isLoading ? (
        <p className="text-sm text-[#6A6A6A]">Cargando mensajes…</p>
      ) : loadError ? (
        <p className="text-sm text-red-600">{loadError}</p>
      ) : (
        <>
          <ul className="space-y-3 mb-4" aria-live="polite">
            {messages.length === 0 && (
              <li className="text-sm text-[#6A6A6A]">
                Aún no hay mensajes. Si tienes dudas sobre tu validación, escríbenos.
              </li>
            )}
            {messages.map((message) => {
              const isMine = message.created_by === ownerUserId;
              const authorName = isMine
                ? 'Tú'
                : message.created_by_user?.name ?? 'Equipo Ñapa';
              return (
                <li
                  key={message.id}
                  className={`flex ${isMine ? 'justify-end' : 'justify-start'}`}
                >
                  <div
                    className={`max-w-[80%] rounded-[14px] px-4 py-3 ${
                      isMine
                        ? 'bg-[#4B236A] text-white'
                        : 'bg-[#F2ECF6] text-[#1A1A1A]'
                    }`}
                  >
                    <div className="flex items-baseline justify-between gap-4 mb-1">
                      <span className="text-xs font-medium opacity-90">{authorName}</span>
                      <span className="text-[11px] opacity-70">
                        {formatDate(message.created_at)}
                      </span>
                    </div>
                    {message.comment_type?.code === 'RJ' && (
                      <span className="inline-block mb-1 text-[11px] font-semibold uppercase tracking-wide opacity-90">
                        Observación de rechazo
                      </span>
                    )}
                    <p className="text-sm whitespace-pre-wrap break-words">{message.comment}</p>
                  </div>
                </li>
              );
            })}
          </ul>

          {canMessage ? (
            <form onSubmit={handleSubmit} className="space-y-2">
              <label htmlFor="provider-message" className="sr-only">
                Escribe un mensaje
              </label>
              <textarea
                id="provider-message"
                value={draft}
                onChange={(event) => setDraft(event.target.value)}
                disabled={isSending}
                rows={3}
                maxLength={500}
                placeholder="Escribe un mensaje para el equipo de validación…"
                className="w-full px-4 py-3 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] resize-none disabled:bg-[#F7F7F7]"
              />
              {sendError && <p className="text-sm text-red-600">{sendError}</p>}
              <div className="flex justify-end">
                <button
                  type="submit"
                  disabled={isSending || !draft.trim()}
                  className="inline-flex items-center gap-2 h-[44px] px-5 bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <Send className="h-4 w-4" />
                  {isSending ? 'Enviando…' : 'Enviar'}
                </button>
              </div>
            </form>
          ) : (
            <p className="text-sm text-[#6A6A6A]">
              El canal de mensajes está disponible únicamente mientras tu comercio está en
              revisión.
            </p>
          )}
        </>
      )}
    </div>
  );
}
