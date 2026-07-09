/**
 * Sección de Comentarios para Validación
 * 
 * Responsabilidades:
 * - Mostrar historial de comentarios del proveedor
 * - Permitir agregar nuevos comentarios
 * - Formatear fechas y autores
 * 
 * TODO: Integrar con API de comentarios del backend
 */

'use client';

import { useState } from 'react';
import { MessageSquare, Send } from 'lucide-react';
import { useProviderValidationComments } from '@/hooks/use-provider-validation-comments';
import { ConfirmationDialog } from '@/components/admin/shared/confirmation-dialog';
import type { CommerceCommentType } from '@/lib/api';

const ADMIN_COMMENT_TYPES: Array<{ value: CommerceCommentType; label: string }> = [
  { value: 'MS', label: 'Mensaje al proveedor' },
  { value: 'VA', label: 'Nota interna' },
];

// ============================================
// Props Interface
// ============================================

interface ProviderValidationCommentsProps {
  providerId: number;
  refreshTrigger?: number;
}

// ============================================
// Mock Data (TODO: Reemplazar con API)
// ============================================

// ============================================
// Component
// ============================================

export function ProviderValidationComments({ providerId, refreshTrigger = 0 }: ProviderValidationCommentsProps) {
  const [newComment, setNewComment] = useState('');
  const [commentType, setCommentType] = useState<CommerceCommentType>('MS');
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const {
    comments,
    isLoading,
    loadError,
    isSubmitting,
    submitError,
    reloadComments,
    addComment,
  } = useProviderValidationComments(providerId, refreshTrigger);

  /**
   * Envía el comentario y limpia el input.
   */
  const doSend = async () => {
    try {
      await addComment(newComment, commentType);
      setNewComment('');
    } catch {
      // El mensaje de error se gestiona en el hook.
    }
  };

  /**
   * Los mensajes al proveedor (MS) piden confirmación por ser visibles al aliado;
   * las notas internas (VA) se envían directamente.
   */
  const handleAddComment = async () => {
    if (!newComment.trim()) return;

    if (commentType === 'MS') {
      setIsConfirmOpen(true);
      return;
    }

    await doSend();
  };

  const handleConfirmSend = async () => {
    setIsConfirmOpen(false);
    await doSend();
  };

  /**
   * Formatea fecha ISO a formato legible
   */
  const formatDate = (isoDate: string): string => {
    try {
      const date = new Date(isoDate);
      return date.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    } catch {
      return 'Fecha inválida';
    }
  };

  return (
    <div className="space-y-4">
      {/* Header */}
      <div className="flex items-center gap-2">
        <MessageSquare className="w-5 h-5 text-[#4B236A]" />
        <h3 className="text-lg font-semibold text-[#1A1A1A]">
          Historial de Comentarios
        </h3>
      </div>

      {/* Lista de Comentarios */}
      <div className="space-y-3 max-h-64 overflow-y-auto">
        {isLoading ? (
          <div className="space-y-3">
            <div className="h-[76px] animate-pulse rounded-xl border border-[#E0E0E0] bg-[#F7F7F7]" />
            <div className="h-[76px] animate-pulse rounded-xl border border-[#E0E0E0] bg-[#F7F7F7]" />
          </div>
        ) : loadError ? (
          <div className="p-6 text-center bg-[#F7F7F7] rounded-xl border border-[#E0E0E0] space-y-3">
            <p className="text-sm text-[#6A6A6A]">{loadError}</p>
            <button
              onClick={() => void reloadComments()}
              className="h-[40px] px-4 rounded-xl border border-[#C8D86D] text-[#4B236A] text-sm font-medium hover:bg-[#DDE8BB]/40 transition-colors"
            >
              Reintentar
            </button>
          </div>
        ) : comments.length === 0 ? (
          <div className="p-6 text-center bg-[#F7F7F7] rounded-xl">
            <p className="text-sm text-[#6A6A6A]">
              No hay comentarios aún
            </p>
          </div>
        ) : (
          comments.map((comment) => (
            <div
              key={comment.id}
              className="p-4 bg-[#F7F7F7] rounded-xl border border-[#E0E0E0]"
            >
              <div className="flex items-baseline justify-between mb-2 gap-2">
                <span className="text-sm font-medium text-[#1A1A1A]">
                  {comment.created_by_user?.name ?? `Usuario #${comment.created_by}`}
                </span>
                <div className="flex items-center gap-2 shrink-0">
                  {comment.comment_type?.name && (
                    <span
                      className={`text-[11px] font-medium px-2 py-0.5 rounded-full ${
                        comment.comment_type.code === 'MS'
                          ? 'bg-[#DDE8BB]/60 text-[#4B236A]'
                          : 'bg-[#E0E0E0] text-[#6A6A6A]'
                      }`}
                    >
                      {comment.comment_type.name}
                    </span>
                  )}
                  <span className="text-xs text-[#6A6A6A]">
                    {formatDate(comment.created_at)}
                  </span>
                </div>
              </div>
              <p className="text-sm text-[#1A1A1A]">{comment.comment}</p>
            </div>
          ))
        )}
      </div>

      {/* Agregar Comentario */}
      {/* Selector de tipo: mensaje visible al proveedor vs nota interna */}
      <div className="flex gap-2" role="radiogroup" aria-label="Tipo de comentario">
        {ADMIN_COMMENT_TYPES.map((option) => {
          const isActive = commentType === option.value;
          return (
            <button
              key={option.value}
              type="button"
              role="radio"
              aria-checked={isActive}
              onClick={() => setCommentType(option.value)}
              disabled={isSubmitting}
              className={`h-[36px] px-3 rounded-full text-sm font-medium border transition-colors disabled:opacity-50 ${
                isActive
                  ? 'bg-[#4B236A] text-white border-[#4B236A]'
                  : 'bg-white text-[#6A6A6A] border-[#E0E0E0] hover:bg-[#F7F7F7]'
              }`}
            >
              {option.label}
            </button>
          );
        })}
      </div>
      <div className="flex gap-2">
        <input
          type="text"
          value={newComment}
          onChange={(e) => setNewComment(e.target.value)}
          onKeyDown={(e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
              e.preventDefault();
              handleAddComment();
            }
          }}
          placeholder={
            commentType === 'MS'
              ? 'Escribe un mensaje para el proveedor...'
              : 'Escribe una nota interna (no visible al proveedor)...'
          }
          disabled={isSubmitting}
          className="flex-1 h-[44px] px-4 border border-[#E0E0E0] rounded-xl focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A]"
        />
        <button
          onClick={handleAddComment}
          disabled={isSubmitting || !newComment.trim()}
          className="px-4 h-[44px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
        >
          <Send className="w-4 h-4" />
          {isSubmitting ? 'Enviando...' : 'Enviar'}
        </button>
      </div>

      {submitError && (
        <div className="p-3 rounded-xl border border-[#E0E0E0] bg-[#F7F7F7]">
          <p className="text-sm text-[#6A6A6A]">{submitError}</p>
        </div>
      )}

      <ConfirmationDialog
        isOpen={isConfirmOpen}
        title="Enviar mensaje al proveedor"
        description="Este mensaje será visible para el proveedor. ¿Deseas enviarlo?"
        confirmText="Sí, enviar"
        cancelText="Cancelar"
        variant="warning"
        isLoading={isSubmitting}
        onConfirm={handleConfirmSend}
        onClose={() => setIsConfirmOpen(false)}
      />
    </div>
  );
}
